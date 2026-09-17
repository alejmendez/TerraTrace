<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StoreDogRequest;
use Modules\Fields\Http\Requests\UpdateDogRequest;
use Modules\Fields\Http\Resources\DogResource;
use Modules\Fields\Services\DogService;
use Modules\Fields\Services\FieldService;
use Modules\Users\Services\UserService;

class DogsController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct(
        private readonly DogService $dogs,
        private readonly FieldService $fields,
        private readonly UserService $users,
    ) {
        $this->setupPermissionMiddleware();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payload = $this->dogs->collection(request()->all());

        return Inertia::render('Fields::Dogs/List', [
            'toast' => session('toast'),
            'records' => $payload['items'],
            'meta' => $payload['meta'],
            'summary' => $payload['summary'],
            'fields' => $this->fields->forSelect(),
            'couples' => $this->users->couples(),
            'genders' => $this->gendersForSelect(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Fields::Dogs/Create', [
            'fields' => $this->fields->forSelect(),
            'couples' => $this->users->couples(),
            'genders' => $this->gendersForSelect(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDogRequest $request)
    {
        $data = $request->validated();
        $data['avatar'] = $this->storeAvatar($request);
        $this->dogs->create($data);

        return redirect()->route('dogs.index')->with('toast', [
            'severity' => 'success',
            'summary' => __('generics.messages.saved_successfully'),
            'detail' => __('generics.messages.saved_successfully'),
            'life' => 5000,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $dog = $this->dogs->find($id);

        return Inertia::render('Fields::Dogs/Show', [
            'data' => new DogResource($dog),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $dog = $this->dogs->find($id);

        return Inertia::render('Fields::Dogs/Edit', [
            'data' => new DogResource($dog),
            'fields' => $this->fields->forSelect(),
            'couples' => $this->users->couples(),
            'genders' => $this->gendersForSelect(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDogRequest $request, string $id)
    {
        $data = $request->validated();
        $data['avatar'] = $this->storeAvatar($request);
        $this->dogs->update($id, $data);

        return redirect()->route('dogs.index')->with('toast', [
            'severity' => 'success',
            'summary' => __('generics.messages.saved_successfully'),
            'detail' => __('generics.messages.saved_successfully'),
            'life' => 5000,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->dogs->delete($id);

        return response()->noContent();
    }

    /**
     * Static {value, text} list of M / F with i18n labels. Kept here
     * because the values are constant; promoting to its own service
     * would be over-engineering for two fixed options.
     */
    private function gendersForSelect(): array
    {
        return [
            ['value' => 'M', 'text' => trans('dog.form.gender.options.male')],
            ['value' => 'F', 'text' => trans('dog.form.gender.options.female')],
        ];
    }

    protected function storeAvatar(UpdateDogRequest|StoreDogRequest $request)
    {
        if ($request->file('avatar') == null) {
            return null;
        }

        return $request->file('avatar')->storePublicly('public/avatars');
    }
}
