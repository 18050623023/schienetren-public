<div>
    @if($user->hasRole('owner') && $tenant->hasFeatureCode(\App\Enums\TenantFeature::MAX_BRANCH))

        <x-filament::input.wrapper>
            <x-filament::input.select 
                wire:model.live="setCurrentBranchId" 
                :disabled="!$hasBypassRole"
            >
                <option value="" >Not Selected</option>

                @foreach($branches as $branch)
                    <option value="{{ $branch['id'] }}" >
                        {{ $branch['name'] }}
                    </option>
                @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
    @endif
</div>

