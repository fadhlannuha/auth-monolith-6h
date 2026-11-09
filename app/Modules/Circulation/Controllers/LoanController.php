<?php

namespace Modules\Circulation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Modules\Circulation\Models\BorrowingLog;
use Modules\Circulation\Models\Loan;
use Modules\Circulation\Requests\StoreLoanRequest;
use Modules\Circulation\Services\LoanService;
use Modules\Shared\Contracts\BookServiceInterface;
use Modules\Shared\Contracts\MemberServiceInterface;
use Modules\Shared\Exceptions\DomainException;

class LoanController extends Controller
{
    public function __construct(
        private readonly BookServiceInterface $bookService,
        private readonly MemberServiceInterface $memberService,
        private readonly LoanService $loanService,
    ) {}

    public function index(Request $request)
    {
        $query = Loan::query()
            ->with(['items', 'returnRecord'])
            ->withCount('items');

        if ($request->boolean('trashed')) {
            abort_unless($request->user()?->role === 'admin', 403);
            $query->onlyTrashed();
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->search($search);
        }

        if ($status = $request->input('status')) {
            $query->byStatus($status);
        }

        if ($memberId = $request->integer('member')) {
            $query->where('member_id', $memberId);
        }

        $loans = $query->latest('borrowed_at')->paginate(10)->withQueryString();

        $stats = [
            'active' => Loan::whereNull('returned_at')->count(),
            'overdue' => Loan::overdue()->count(),
            'returned' => Loan::whereNotNull('returned_at')->count(),
            'logs' => BorrowingLog::count(),
        ];

        return view('circulation.loans.index', compact('loans', 'stats'));
    }

    public function create()
    {
        return view('circulation.loans.create', [
            'members' => $this->memberService->getSelectableMembers(),
            'books' => $this->bookService->getAvailableBooks(),
        ]);
    }

    public function store(StoreLoanRequest $request)
    {
        try {
            $loan = $this->loanService->borrow(
                memberId: $request->integer('member_id'),
                items: $request->validated('items'),
                loanDays: $request->integer('loan_days'),
                processedBy: $request->user()->id,
                notes: $request->input('notes'),
            );
        } catch (DomainException|InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->withErrors(['loan' => $exception->getMessage()]);
        }

        return redirect()
            ->route('circulation.loans.show', $loan)
            ->with('success', 'Peminjaman berhasil dibuat.');
    }

    public function show(Loan $loan)
    {
        $loan->load(['items', 'returnRecord', 'logs']);

        return view('circulation.loans.show', compact('loan'));
    }

    public function returnLoan(Request $request, Loan $loan)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $loan = $this->loanService->returnLoan(
                loan: $loan,
                processedBy: $request->user()->id,
                notes: $validated['notes'] ?? null,
            );
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('circulation.loans.show', $loan)
            ->with('success', 'Buku berhasil dikembalikan dengan denda '.number_format((float) $loan->fine_amount, 0, ',', '.').' rupiah.');
    }

    public function restore(Loan $loan)
    {
        $loan->restore();

        return redirect()
            ->route('circulation.loans.index')
            ->with('success', 'Peminjaman berhasil dipulihkan.');
    }
}
