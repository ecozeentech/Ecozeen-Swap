<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'about',
                'title' => 'Bridging the Gap to Financial Inclusion',
                'meta_title' => 'About Us — Ecozeen Swap',
                'meta_description' => 'Ecozeen Tech Ltd is a technology company bridging the gap between traditional finance and decentralized ecosystems across NG & UK.',
                'content' => <<<'HTML'
<p>Founded with a vision to democratize access to high-fidelity financial tools, Ecozeen Swap emerged from the intersection of institutional trading demands and the need for eco-conscious infrastructure. Operating across Nigeria and the United Kingdom, our mission is to build resilient systems that empower users with absolute control over their assets while ensuring uncompromising security and frictionless liquidity.</p>
<p>Unlike peer-to-peer marketplaces, Ecozeen Swap is the sole counter-party for every trade &mdash; when you buy, sell, or swap, you are always trading directly with us, at rates we publish and stand behind.</p>
HTML,
            ],
            [
                'slug' => 'contact',
                'title' => 'Get in Touch',
                'meta_title' => 'Contact Us — Ecozeen Swap',
                'meta_description' => 'Our institutional support team is available 24/7. Reach out for technical assistance, partnership inquiries, or general support.',
                'content' => <<<'HTML'
<p>Our institutional support team is available around the clock. Whether you have a question about your account, need help with a trade, or want to explore a partnership, send us a message and we will respond as soon as possible.</p>
HTML,
            ],
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'meta_title' => 'Privacy Policy — Ecozeen Swap',
                'meta_description' => 'How Ecozeen Swap collects, uses, and protects your personal information.',
                'content' => <<<'HTML'
<h2>1. Introduction</h2>
<p>Welcome to Ecozeen Swap. We are committed to protecting your personal information and your right to privacy. If you have any questions or concerns about this privacy notice, or our practices with regards to your personal information, please contact us at privacy@ecozeenswap.com.</p>
<p>When you visit our website and use any of our services, we appreciate that you are trusting us with your personal information. We take your privacy very seriously. In this privacy notice, we seek to explain to you in the clearest way possible what information we collect, how we use it, and what rights you have in relation to it.</p>
<h2>2. Data Collection</h2>
<p>We collect personal information that you voluntarily provide to us when you register on the Services, express an interest in obtaining information about us or our products and Services, when you participate in activities on the Services, or otherwise when you contact us.</p>
<ul>
<li><strong>Personal Information Provided by You.</strong> We collect names, phone numbers, email addresses, mailing addresses, contact preferences, data collected from surveys, passwords, contact or authentication data, and other similar information.</li>
<li><strong>Payment Data.</strong> We may collect data necessary to process your payment if you make purchases, such as your payment instrument number and the security code associated with your payment instrument.</li>
<li><strong>Transaction Data.</strong> Details of transactions you carry out through our platform and of the fulfilment of your orders.</li>
</ul>
<h2>3. Security</h2>
<p>We have implemented appropriate technical and organizational security measures designed to protect the security of any personal information we process. However, despite our safeguards and efforts to secure your information, no electronic transmission over the internet or information storage technology can be guaranteed to be 100% secure.</p>
<p>We utilize advanced encryption protocols and multi-factor authentication to ensure your assets and data remain secure at all times.</p>
<h2>4. Cookies</h2>
<p>We may use cookies and similar tracking technologies (like web beacons and pixels) to access or store information. Specific information about how we use such technologies and how you can refuse certain cookies is set out in our Cookie Notice.</p>
<h2>5. Your Rights</h2>
<p>You may request access to, correction of, or deletion of your personal data at any time by contacting our support team, subject to our legal and regulatory record-keeping obligations.</p>
HTML,
            ],
            [
                'slug' => 'terms-and-conditions',
                'title' => 'Terms & Conditions',
                'meta_title' => 'Terms & Conditions — Ecozeen Swap',
                'meta_description' => 'The terms and conditions governing your use of Ecozeen Swap.',
                'content' => <<<'HTML'
<h2>1. Introduction</h2>
<p>These Terms and Conditions govern your use of the Ecozeen Swap platform ("Platform"). By accessing or using our services, you agree to be bound by these terms. Ecozeen Swap provides institutional-grade cryptocurrency trading and decentralized financial services.</p>
<h2>2. User Obligations</h2>
<h3>2.1 Eligibility</h3>
<p>You must be at least 18 years of age and possess the legal authority to form a binding contract to use this Platform. Institutional users must assure they have the necessary corporate authorizations.</p>
<h3>2.2 Account Security</h3>
<ul>
<li>Users are solely responsible for maintaining the confidentiality of their account credentials, private keys, and multi-factor authentication devices.</li>
<li>Ecozeen Swap will never ask for your private keys. Any such request should be considered fraudulent.</li>
<li>You must notify us immediately upon discovering any unauthorized use of your account.</li>
</ul>
<h2>3. Trading Rules and Execution</h2>
<h3>3.1 Order Execution</h3>
<p>Orders placed on the Platform are executed based on the daily buy/sell rates published by Ecozeen Swap. Ecozeen Swap does not guarantee the availability of a specific rate outside its published 24-hour validity window.</p>
<h3>3.2 Prohibited Activities</h3>
<p>Users are strictly prohibited from engaging in:</p>
<ol>
<li>Market manipulation, including spoofing, wash trading, or any activity designed to artificially affect asset prices.</li>
<li>Utilizing the Platform for money laundering, terrorist financing, or any other illegal financial activity.</li>
<li>Attempting to bypass our security measures or reverse engineer the systems powering the platform.</li>
</ol>
<h2>4. Limitation of Liability</h2>
<p>To the maximum extent permitted by applicable law, Ecozeen Swap, its affiliates, and its operators shall not be liable for any indirect, incidental, special, consequential, or punitive damages, including loss of profits, data, or digital assets, resulting from your use of or inability to use the Platform. Trading digital assets involves significant risk.</p>
<h2>5. Modifications to Terms</h2>
<p>Ecozeen Swap reserves the right to modify these Terms at any time. We will provide notice of significant changes via the Platform. Continued use of the Platform after such modifications constitutes acceptance of the updated terms.</p>
HTML,
            ],
        ];

        foreach ($pages as $page) {
            Page::query()->updateOrCreate(['slug' => $page['slug']], array_merge($page, ['is_published' => true]));
        }
    }
}
