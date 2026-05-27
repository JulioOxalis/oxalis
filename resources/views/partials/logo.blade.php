@if(config('oxalis.brand.logo_url'))
<div class="ox-brand-logo text-center mb-3">
    <img src="{{ config('oxalis.brand.logo_url') }}"
         alt="{{ config('app.name') }}"
         style="max-height:52px;width:auto;object-fit:contain">
</div>
@endif
