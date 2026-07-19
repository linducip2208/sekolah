<?php

namespace App\Services\SEO;

class StructuredDataBuilder
{
    public function educationalOrganization(array $data): array
    {
        return array_filter([
            '@context'      => 'https://schema.org',
            '@type'         => 'EducationalOrganization',
            'name'          => $data['name'] ?? null,
            'description'   => $data['description'] ?? null,
            'url'           => $data['url'] ?? null,
            'logo'          => $data['logo'] ?? null,
            'address'       => isset($data['address']) ? [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $data['address'] ?? null,
                'addressLocality' => $data['city'] ?? null,
                'addressRegion'   => $data['region'] ?? null,
                'postalCode'      => $data['zip'] ?? null,
                'addressCountry'  => $data['country'] ?? 'ID',
            ] : null,
            'telephone'     => $data['phone'] ?? null,
            'email'         => $data['email'] ?? null,
            'foundingDate'  => $data['founding_date'] ?? null,
        ]);
    }

    public function itemList(string $name, array $items): array
    {
        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'ItemList',
            'name'            => $name,
            'numberOfItems'   => count($items),
            'itemListElement' => array_map(function ($item, $i) {
                return [
                    '@type'    => 'ListItem',
                    'position' => $i + 1,
                    'item'     => array_filter([
                        '@type'       => $item['type'] ?? 'Thing',
                        'name'        => $item['name'] ?? null,
                        'description' => $item['description'] ?? null,
                        'url'         => $item['url'] ?? null,
                        'image'       => $item['image'] ?? null,
                    ]),
                ];
            }, $items, array_keys($items)),
        ];
    }

    public function event(array $data): array
    {
        return array_filter([
            '@context'    => 'https://schema.org',
            '@type'       => 'Event',
            'name'        => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'startDate'   => $data['starts_at'] ?? null,
            'endDate'     => $data['ends_at'] ?? null,
            'location'    => isset($data['venue']) ? [
                '@type'   => 'Place',
                'name'    => $data['venue'] ?? null,
                'address' => $data['city'] ?? null,
            ] : null,
            'organizer'   => isset($data['organizer']) ? [
                '@type' => 'EducationalOrganization',
                'name'  => $data['organizer'] ?? null,
            ] : null,
            'offers'      => isset($data['ticket_price']) ? [
                '@type'         => 'Offer',
                'price'         => intval(($data['ticket_price'] ?? 0) / 100),
                'priceCurrency' => 'IDR',
                'availability'  => 'https://schema.org/InStock',
            ] : null,
            'image'       => $data['image'] ?? null,
        ]);
    }

    public function donationCampaign(array $data): array
    {
        return array_filter([
            '@context'        => 'https://schema.org',
            '@type'           => 'MonetaryGrant',
            'name'            => $data['title'] ?? null,
            'description'     => $data['description'] ?? null,
            'amount'          => isset($data['target_amount']) ? [
                '@type'    => 'MonetaryAmount',
                'currency' => 'IDR',
                'value'    => intval($data['target_amount'] / 100),
            ] : null,
            'fundedItem'      => isset($data['campaign_purpose']) ? [
                '@type' => 'Thing',
                'name'  => $data['campaign_purpose'],
            ] : null,
            'sponsor'         => isset($data['school_name']) ? [
                '@type' => 'EducationalOrganization',
                'name'  => $data['school_name'],
            ] : null,
        ]);
    }

    public function faqPage(array $faqs): array
    {
        return [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => array_map(fn ($f) => [
                '@type'          => 'Question',
                'name'           => $f['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => $f['answer'],
                ],
            ], $faqs),
        ];
    }

    public function breadcrumb(array $items): array
    {
        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => array_map(fn ($it, $i) => [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $it['name'],
                'item'     => $it['url'] ?? null,
            ], $items, array_keys($items)),
        ];
    }
}
