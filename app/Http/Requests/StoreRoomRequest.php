<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'avatar' => ['nullable', 'string', 'max:255'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['integer', 'exists:users,id'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $memberIds = collect($this->input('member_ids', []))
            ->merge($this->input('user_ids', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $this->merge([
            'member_ids' => $memberIds,
        ]);
    }
}
