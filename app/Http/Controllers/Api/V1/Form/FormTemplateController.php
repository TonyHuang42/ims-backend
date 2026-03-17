<?php

namespace App\Http\Controllers\Api\V1\Form;

use App\Http\Controllers\Controller;
use App\Http\Requests\Form\StoreFormTemplateRequest;
use App\Http\Requests\Form\UpdateFormTemplateRequest;
use App\Http\Resources\Form\FormTemplateResource;
use App\Models\FormTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class FormTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = FormTemplate::query()->with('creator');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        if (! $request->user()->can('viewInactive', FormTemplate::class)) {
            $query->where('is_active', true);
        }

        $perPage = $request->integer('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        return FormTemplateResource::collection($query->paginate($perPage));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFormTemplateRequest $request): FormTemplateResource
    {
        $template = FormTemplate::create([
            ...$request->validated(),
            'created_by' => Auth::guard('api')->id(),
        ]);

        return new FormTemplateResource($template->load('creator'));
    }

    /**
     * Display the specified resource.
     */
    public function show(FormTemplate $formTemplate): FormTemplateResource
    {
        $this->authorize('view', $formTemplate);

        return new FormTemplateResource($formTemplate->load('creator'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFormTemplateRequest $request, FormTemplate $formTemplate): FormTemplateResource
    {
        $formTemplate->update($request->validated());

        return new FormTemplateResource($formTemplate->load('creator'));
    }
}
