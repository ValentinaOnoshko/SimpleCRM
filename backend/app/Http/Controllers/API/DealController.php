<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Deal;
use App\Http\Resources\DealResource;
use App\Enums\DealStatus;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class DealController extends Controller
{
	public function __construct(private readonly CacheService $cacheService) {}

	public function index(Request $request): JsonResponse
	{
		$cacheKey = $this->cacheService->generateCacheKey('deals', [
			'user_id' => $request->user()->getId(),
			'role' => $request->user()->getRole(),
			'search' => $request->input('search', ''),
			'status' => $request->input('status', ''),
			'sort_by' => $request->input('sort_by', 'created_at'),
			'sort_order' => $request->input('sort_order', 'desc'),
			'page' => $request->input('page', 1),
		]);

		$deals = $this->cacheService->get($cacheKey, function () use ($request) {
			$query = Deal::with('creator', 'performer', 'comments.user');

			$user = $request->user();
			if ($user->getRole() === 'client') {
				$query->where('client_id', $user->getId());
			} elseif ($user->getRole() === 'performer') {
				$query->where('performer_id', $user->getId());
			}

			if ($request->has('search')) {
				$search = $request->input('search');
				$query->where(function($q) use ($search) {
					$q->where('title', 'like', "%$search%")
						->orWhere('topic', 'like', "%$search%")
						->orWhere('description', 'like', "%$search%");
				});
			}

			if ($request->has('status') && DealStatus::tryFrom($request->input('status'))) {
				$query->where('status', $request->input('status'));
			}

			$sortField = $request->input('sort_by', 'created_at');
			$sortOrder = $request->input('sort_order', 'desc');
			$query->orderBy($sortField, $sortOrder);

			return $query->paginate(10);
		}, 60);

		return response()->json(DealResource::collection($deals));
	}

	public function show(Deal $deal): JsonResponse
	{
		$deal->load('creator', 'performer', 'comments.user');
		return response()->json(new DealResource($deal));
	}

	public function store(Request $request): JsonResponse
	{
		$request->validate([
			'title' => 'required|string|max:255',
			'description' => 'required|string',
			'status' => 'required|in:' . implode(',', array_column(DealStatus::cases(), 'value')),
		]);

		$validated = $request->all();
		$validated['client_id'] = $request->user()->getId();

		$deal = Deal::create($validated);
		$this->cacheService->clear('deals');
		return response()->json(new DealResource($deal), 201);
	}

	public function update(Request $request, Deal $deal): JsonResponse
	{
		$request->validate([
			'title' => 'sometimes|required|string|max:255',
			'description' => 'sometimes|required|string',
			'status' => 'sometimes|required|in:' . implode(',',
					array_column(DealStatus::cases(), 'value')),
			'performer_id' => 'sometimes|nullable|exists:users,id'
		]);

		$validated = $request->only(['title', 'description', 'status', 'performer_id']);
		$deal->update($validated);
		$this->cacheService->clear('deals');
		return response()->json(new DealResource($deal));
	}

	public function destroy(Deal $deal): JsonResponse
	{
		$deal->delete();
		$this->cacheService->clear('deals');
		return response()->json(null, 204);
	}

	public function getComments(Deal $deal): JsonResponse
	{
		$comments = $deal->comments()
			->with('user')
			->latest()
			->take(10)
			->get();
		return response()->json(CommentResource::collection($comments));
	}

	public function updateStatus(Request $request, Deal $deal): JsonResponse
	{
		$request->validate([
			'status' => ['required', Rule::enum(DealStatus::class)]
		]);

		$deal->update(['status' => $request->input('status')]);
		$this->cacheService->clear('deals');
		return response()->json(new DealResource($deal));
	}
}
