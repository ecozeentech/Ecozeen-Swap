<?php

namespace App\Support;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;

/**
 * Editable copy and images for the public marketing pages.
 *
 * Nothing is stored until an admin saves. Until then every section
 * falls back to the same text the templates used to hardcode, so a
 * fresh install looks unchanged. Privacy, terms, the About story body,
 * and the contact intro stay on the Pages editor (HTML). Phone, email,
 * and address stay on Settings.
 */
class SiteContent
{
    public const GROUP = 'content';

    /**
     * @return array<string, array{label: string, description: string, sections: array<string, array<string, mixed>>}>
     */
    public static function groups(): array
    {
        return [
            'homepage' => [
                'label' => 'Homepage',
                'description' => 'Hero, stats, feature cards, how-it-works, and the closing call to action. Live rates stay automatic.',
                'sections' => [
                    'seo' => self::block('Search & social title', [
                        'title' => self::text('Page title', 'Ecozeen Swap — Buy, Sell & Swap Crypto Securely', 180),
                        'description' => self::textarea('Meta description', 'Ecozeen Swap — buy, sell, and swap crypto directly with Ecozeen Tech Ltd, your single trusted vendor. No P2P, no third-party ads.'),
                    ]),
                    'hero' => self::block('Hero', [
                        'badge' => self::text('Badge', 'Ecozeen Tech Ltd · Single Trusted Vendor'),
                        'headline' => self::text('Headline', 'Buy, Sell & Swap Crypto —', 200),
                        'headline_highlight' => self::text('Highlighted phrase', 'directly with us.', 200),
                        'subtitle' => self::textarea('Subtitle', 'No peer-to-peer ads, no third-party sellers, no waiting on strangers. Ecozeen Swap is the counter-party for every trade, with transparent daily rates and fast settlement.'),
                        'primary_cta' => self::text('Primary button', 'Create Free Account'),
                        'secondary_cta' => self::text('Secondary button', 'Log In'),
                    ], 'Button links stay pointed at registration and login.'),
                    'stats' => self::repeat('Hero stats', [
                        'value' => self::text('Value', ''),
                        'label' => self::text('Label', ''),
                    ], [
                        ['value' => '24h', 'label' => 'Rate refresh cycle'],
                        ['value' => '0', 'label' => 'P2P ads or listings'],
                        ['value' => '1', 'label' => 'Trusted counter-party'],
                    ]),
                    'rate_board' => self::block('Rate board', [
                        'title' => self::text('Title', "Today's Rate Board"),
                        'empty_label' => self::text('Label when no rates are live', 'Coming online'),
                        'footnote' => self::textarea('Footnote', 'Sign in to see all active rates with a live 24-hour countdown.'),
                    ], 'The pairs and prices themselves come from Daily Rates.'),
                    'features_intro' => self::block('Features heading', [
                        'title' => self::text('Title', 'Everything you need, one vendor you can trust', 200),
                        'subtitle' => self::textarea('Subtitle', 'A complete crypto toolkit — trading, gift cards, and invoicing — all settled directly with Ecozeen Swap.'),
                    ]),
                    'features' => self::repeat('Feature cards', [
                        'title' => self::text('Title', ''),
                        'description' => self::textarea('Description', ''),
                        'image' => self::image('Icon image (optional)'),
                    ], [
                        ['title' => 'Buy Crypto', 'description' => 'Fund via card, bank transfer, or Paystack/Flutterwave at our published sell rate.'],
                        ['title' => 'Sell Crypto', 'description' => 'Send crypto to your dedicated address and get paid out in your local currency.'],
                        ['title' => 'Swap Instantly', 'description' => 'Convert between assets with a 5-minute rate lock — no manual approvals.'],
                        ['title' => 'Gift Cards & Invoices', 'description' => 'Cash out gift cards or bill clients in crypto with a shareable invoice link.'],
                    ], 'Leave the image empty to keep the built-in icon. Remove a card with its remove button.'),
                    'steps_intro' => self::block('How it works heading', [
                        'title' => self::text('Title', 'How it works', 200),
                        'subtitle' => self::textarea('Subtitle', ''),
                    ]),
                    'steps' => self::repeat('Steps', [
                        'number' => self::text('Number', '', 12),
                        'title' => self::text('Title', ''),
                        'description' => self::textarea('Description', ''),
                    ], [
                        ['number' => '1', 'title' => 'Create your account', 'description' => 'Sign up with a username and email, then verify your identity to unlock higher trading limits.'],
                        ['number' => '2', 'title' => 'Fund your wallet', 'description' => 'Deposit fiat via Paystack, Flutterwave, or bank transfer — or send crypto to your dedicated address.'],
                        ['number' => '3', 'title' => 'Trade instantly', 'description' => 'Buy, sell, or swap at our published rates. Every trade settles directly with Ecozeen Swap.'],
                    ]),
                    'cta' => self::block('Closing banner', [
                        'title' => self::text('Title', 'Ready to start trading?', 200),
                        'subtitle' => self::textarea('Subtitle', 'Join Ecozeen Swap today and trade crypto with a vendor you can trust.'),
                        'button' => self::text('Button', 'Create Free Account'),
                    ], 'The button always links to registration.'),
                ],
            ],
            'about' => [
                'label' => 'About',
                'description' => 'Headings, cards, team photos, and the side image. The Our Story paragraphs are edited under Pages → About.',
                'sections' => [
                    'hero' => self::block('Hero', [
                        'title' => self::text('Title', 'Bridging the Gap to', 200),
                        'title_highlight' => self::text('Highlighted phrase', 'Financial Inclusion', 200),
                        'intro' => self::textarea('Intro', 'Ecozeen Tech Ltd is a technology company bridging the gap between traditional finance and decentralized ecosystems across NG & UK.'),
                    ]),
                    'story' => self::block('Story panel', [
                        'heading' => self::text('Heading', 'Our Story'),
                        'caption' => self::text('Image caption', 'Global Reach'),
                        'image' => self::image('Side image'),
                    ], 'The story paragraphs themselves are the About page body under Pages.'),
                    'advantages_intro' => self::block('Advantages heading', [
                        'title' => self::text('Title', 'The Ecozeen Advantage', 200),
                        'subtitle' => self::textarea('Subtitle', 'Engineered for precision. Built for trust.'),
                    ]),
                    'advantages' => self::repeat('Advantage cards', [
                        'title' => self::text('Title', ''),
                        'description' => self::textarea('Description', ''),
                        'image' => self::image('Icon image (optional)'),
                    ], [
                        ['title' => 'Frictionless Speed', 'description' => 'Institutional-grade execution engines ensure your trades settle instantly, minimizing slippage even in volatile markets.'],
                        ['title' => 'Uncompromising Security', 'description' => 'Encrypted data at rest, real-time activity monitoring, and strict verification protocols form the bedrock of our trust architecture.'],
                        ['title' => 'Dedicated Support', 'description' => 'Our specialized support team provides rapid, precise assistance, ensuring your operations never experience downtime.'],
                    ]),
                    'team_intro' => self::block('Leadership heading', [
                        'title' => self::text('Title', 'Leadership Team', 200),
                        'subtitle' => self::textarea('Subtitle', ''),
                    ]),
                    'team' => self::repeat('Team members', [
                        'name' => self::text('Name', ''),
                        'role' => self::text('Role', ''),
                        'photo' => self::image('Photo'),
                    ], [
                        ['name' => 'Executive Name', 'role' => 'Chief Executive Officer'],
                        ['name' => 'Executive Name', 'role' => 'Chief Technology Officer'],
                    ], 'Clear every member and the heading to hide this block.'),
                    'stats' => self::repeat('Registration stats', [
                        'value' => self::text('Value', ''),
                        'label' => self::text('Label', ''),
                    ], [
                        ['value' => 'RC 1835204', 'label' => 'Registered in Nigeria'],
                        ['value' => '16582062', 'label' => 'Registered in the United Kingdom'],
                        ['value' => '1', 'label' => 'Trusted counter-party for every trade'],
                    ]),
                ],
            ],
            'blog' => [
                'label' => 'Blog',
                'description' => 'The blog listing header. Individual posts and categories are edited under Blog Posts.',
                'sections' => [
                    'header' => self::block('Listing header', [
                        'meta_title' => self::text('Browser title', 'Blog', 180),
                        'description' => self::textarea('Meta description', 'Insights, product updates, and market notes from the Ecozeen Swap team.'),
                        'badge' => self::text('Badge', 'Blog'),
                        'title' => self::text('Heading', 'Insights & Updates', 200),
                        'subtitle' => self::textarea('Subtitle', 'News, market notes, and product updates from the Ecozeen Swap team.'),
                        'all_label' => self::text('All-posts filter label', 'All'),
                    ], 'When a category is open, the heading shows that category name.'),
                ],
            ],
            'navigation' => [
                'label' => 'Navigation',
                'description' => 'Labels in the public header. The links themselves stay pointed at the right pages.',
                'sections' => [
                    'labels' => self::block('Header labels', [
                        'buy' => self::text('Buy', 'Buy'),
                        'sell' => self::text('Sell', 'Sell'),
                        'swap' => self::text('Swap', 'Swap'),
                        'blog' => self::text('Blog', 'Blog'),
                        'about' => self::text('About', 'About'),
                        'contact' => self::text('Contact', 'Contact'),
                        'login' => self::text('Log in', 'Log in'),
                        'register' => self::text('Get started', 'Get Started'),
                        'logout' => self::text('Log out', 'Log Out'),
                        'dashboard' => self::text('Dashboard', 'Dashboard'),
                    ]),
                ],
            ],
            'footer' => [
                'label' => 'Footer',
                'description' => 'Tagline, copyright line, and column labels. Link targets stay fixed.',
                'sections' => [
                    'brand' => self::block('Brand', [
                        'tagline' => self::textarea('Tagline', 'The single trusted vendor for buying, selling, and swapping crypto.'),
                        'copyright' => self::textarea('Copyright line', 'Ecozeen Tech Ltd. Registered in Nigeria (RC 1835204) and the United Kingdom (16582062). All rights reserved.', 500),
                    ], 'The year is added automatically in front of the copyright line.'),
                    'links' => self::block('Columns', [
                        'company_heading' => self::text('Company heading', 'Company'),
                        'about_label' => self::text('About link', 'About Us'),
                        'contact_label' => self::text('Contact link', 'Contact Us'),
                        'blog_label' => self::text('Blog link', 'Blog'),
                        'legal_heading' => self::text('Legal heading', 'Legal'),
                        'privacy_label' => self::text('Privacy link', 'Privacy Policy'),
                        'terms_label' => self::text('Terms link', 'Terms & Conditions'),
                        'start_heading' => self::text('Get started heading', 'Get Started'),
                        'register_label' => self::text('Create account link', 'Create Account'),
                        'login_label' => self::text('Log in link', 'Log In'),
                        'dashboard_label' => self::text('Dashboard link', 'Dashboard'),
                    ]),
                ],
            ],
            'contact' => [
                'label' => 'Contact',
                'description' => 'Form labels and the sidebar headings. Phone, email, and address are edited under Settings. The intro under the page title is the Contact page body under Pages.',
                'sections' => [
                    'labels' => self::block('Labels', [
                        'form_heading' => self::text('Form heading', 'Send a Message'),
                        'name_label' => self::text('Name label', 'Full Name'),
                        'name_placeholder' => self::text('Name placeholder', 'Jane Doe'),
                        'email_label' => self::text('Email label', 'Email'),
                        'email_placeholder' => self::text('Email placeholder', 'jane@company.com'),
                        'subject_label' => self::text('Subject label', 'Subject'),
                        'message_label' => self::text('Message label', 'Message'),
                        'message_placeholder' => self::text('Message placeholder', 'How can we assist you today?'),
                        'submit_label' => self::text('Submit button', 'Submit Inquiry'),
                        'success_message' => self::textarea('Success message', "Thanks for reaching out! We'll get back to you shortly."),
                        'info_heading' => self::text('Sidebar heading', 'Contact Information'),
                        'hq_label' => self::text('Address heading', 'Global Headquarters'),
                        'email_heading' => self::text('Email heading', 'Support Email'),
                        'phone_heading' => self::text('Phone heading', 'Phone'),
                        'live_title' => self::text('Live support heading', 'Live Support'),
                        'live_body' => self::textarea('Live support text', 'Need immediate assistance? Use the live chat widget in the corner of your screen for real-time support.'),
                        'connect_heading' => self::text('Connect heading', 'Connect'),
                    ]),
                    'subjects' => self::repeat('Subject options', [
                        'label' => self::text('Option', ''),
                    ], [
                        ['label' => 'Technical Support'],
                        ['label' => 'Account Issue'],
                        ['label' => 'Partnership Inquiry'],
                        ['label' => 'Compliance / KYC'],
                        ['label' => 'General Inquiry'],
                    ], 'These are the choices in the subject dropdown. At least one is required.', true),
                ],
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function sections(string $group): array
    {
        return self::groups()[$group]['sections'] ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public static function definition(string $group, string $section): array
    {
        return self::sections($group)[$section] ?? [];
    }

    public static function storageKey(string $group, string $section): string
    {
        return 'content.'.$group.'.'.$section;
    }

    /**
     * Saved section, or the built-in default when an admin has never saved it.
     *
     * @return array<int|string, mixed>
     */
    public static function data(string $group, string $section): array
    {
        $definition = self::definition($group, $section);

        if ($definition === []) {
            return [];
        }

        $stored = SystemSetting::get(self::storageKey($group, $section), null);

        if (! is_array($stored)) {
            return self::defaultData($definition);
        }

        if (self::isRepeat($definition)) {
            return array_values(array_filter($stored, 'is_array'));
        }

        $out = [];

        foreach ($definition['fields'] as $field => $meta) {
            if (array_key_exists($field, $stored) && is_string($stored[$field])) {
                $out[$field] = $stored[$field];
            } else {
                $out[$field] = (string) ($meta['default'] ?? '');
            }
        }

        return $out;
    }

    public static function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    public static function isRepeat(array $definition): bool
    {
        return (bool) ($definition['repeat'] ?? false);
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<int|string, mixed>
     */
    public static function defaultData(array $definition): array
    {
        if (self::isRepeat($definition)) {
            return array_values($definition['default'] ?? []);
        }

        $out = [];

        foreach ($definition['fields'] as $field => $meta) {
            $out[$field] = (string) ($meta['default'] ?? '');
        }

        return $out;
    }

    /**
     * @param  array<string, array<string, mixed>>  $fields
     * @return array<string, mixed>
     */
    protected static function block(string $label, array $fields, ?string $help = null): array
    {
        return [
            'label' => $label,
            'help' => $help,
            'repeat' => false,
            'fields' => $fields,
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $item
     * @param  array<int, array<string, string>>  $default
     * @return array<string, mixed>
     */
    protected static function repeat(string $label, array $item, array $default, ?string $help = null, bool $fallbackWhenEmpty = false, int $max = 12): array
    {
        return [
            'label' => $label,
            'help' => $help,
            'repeat' => true,
            'max' => $max,
            'fallback_when_empty' => $fallbackWhenEmpty,
            'item' => $item,
            'default' => $default,
        ];
    }

    /**
     * @return array{type: string, label: string, default: string, max: int}
     */
    protected static function text(string $label, string $default, int $max = 300): array
    {
        return ['type' => 'text', 'label' => $label, 'default' => $default, 'max' => $max];
    }

    /**
     * @return array{type: string, label: string, default: string, max: int}
     */
    protected static function textarea(string $label, string $default, int $max = 2000): array
    {
        return ['type' => 'textarea', 'label' => $label, 'default' => $default, 'max' => $max];
    }

    /**
     * @return array{type: string, label: string, default: string, max: int}
     */
    protected static function image(string $label): array
    {
        return ['type' => 'image', 'label' => $label, 'default' => '', 'max' => 0];
    }
}
