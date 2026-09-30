<?php
namespace Oxalis\View\Components;

use Illuminate\View\Component;

class UserMenu extends Component
{
    public function render()
    {
        return view('oxalis::components.user-menu');
    }
}
