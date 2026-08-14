{{-- Shared section composition — single source of order + data binding. --}}
<div id="konten-utama">
    <x-landing.hero :theme="$theme" :landing="$landing" />
    <x-landing.value-props :theme="$theme" :landing="$landing" />
    <x-landing.product-preview :theme="$theme" :landing="$landing" />
    <x-landing.features :theme="$theme" :landing="$landing" />
    <x-landing.solutions :theme="$theme" :landing="$landing" />
    <x-landing.steps :theme="$theme" :landing="$landing" />
    <x-landing.benefits :theme="$theme" :landing="$landing" />
    <x-landing.security :theme="$theme" :landing="$landing" />
    <x-landing.integrations :theme="$theme" :landing="$landing" />
    <x-landing.demo :theme="$theme" :landing="$landing" />
    <x-landing.testimonials :theme="$theme" :landing="$landing" />
    <x-landing.pricing :theme="$theme" :landing="$landing" :plans="$plans" />
    <x-landing.faq :theme="$theme" :landing="$landing" />
    <x-landing.cta :theme="$theme" />
</div>
