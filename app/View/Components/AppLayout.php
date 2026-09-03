<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    public string $headerTitle;

    public function __construct(string $headerTitle = 'Portfolio aanmaken')
    {
        $this->headerTitle = $headerTitle;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app', ['headerTitle' => $this->headerTitle]);
    }
}
