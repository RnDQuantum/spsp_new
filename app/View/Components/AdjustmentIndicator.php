<?php

namespace App\View\Components;

use App\Services\DynamicStandardService;
use Illuminate\View\Component;

class AdjustmentIndicator extends Component
{
    public bool $hasAdjustments = false;

    public ?string $categoryCode = null;

    public ?int $templateId = null;

    public string $size = 'default'; // 'sm', 'default', 'lg'

    public string $position = 'inline'; // 'inline', 'block', 'float'

    public bool $showIcon = true;

    public ?string $customLabel = null;

    /**
     * Create a new component instance.
     */
    public function __construct(
        ?int $templateId = null,
        ?string $categoryCode = null,
        string $size = 'default',
        string $position = 'inline',
        bool $showIcon = true,
        ?string $customLabel = null
    ) {
        $this->templateId = $templateId;
        $this->categoryCode = $categoryCode;
        $this->size = $size;
        $this->position = $position;
        $this->showIcon = $showIcon;
        $this->customLabel = $customLabel;

        // Check if adjustments exist
        if ($templateId && $categoryCode) {
            // 🛡️ SAFETY: Preload cache to ensure hasCategoryAdjustments() works correctly
            // This prevents silent failures if cache hasn't been preloaded by parent component
            \App\Services\Cache\AspectCacheService::preloadByTemplate($templateId);
            
            $dynamicService = app(DynamicStandardService::class);
            $this->hasAdjustments = $dynamicService->hasCategoryAdjustments($templateId, $categoryCode);
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.adjustment-indicator');
    }
}
