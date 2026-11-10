<div>
    <livewire:components.universal-table-selector 
        :preset="$preset" 
        :params="$params ?? []"
        :state-path="$statePath"
        :event-name="$eventName ?? null"
        :key="'table-selector-'.str()->random(10)" 
    />
</div>
