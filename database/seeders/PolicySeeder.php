<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Policies;

class PolicySeeder extends Seeder
{
    public function run(): void
    {
        $policies = [
            [
                'type' => 'terms',
                'title' => 'Terms & Conditions of Service',
                'text' => <<<TEXT
By accessing or using our platform, you agree to be bound by these Terms & Conditions. These terms apply to all users of the service, including visitors, registered users, and others who interact with any part of our platform.

You must use our services only for lawful purposes. Any use of our services that violates local, national, or international laws is strictly prohibited. We reserve the right to terminate or restrict your access if we believe you have violated these conditions.

All intellectual property on our platform, including text, graphics, logos, and software, is the property of our company. You may not copy, modify, or distribute any content without explicit permission.

We reserve the right to update these Terms at any time. Continued use of our platform constitutes your agreement to the revised terms. It is your responsibility to review this page regularly.
TEXT,
            ],
            [
                'type' => 'privacy',
                'title' => 'Privacy Policy',
                'text' => <<<TEXT
Your privacy is very important to us. This Privacy Policy outlines how we collect, use, and protect the information you provide when you use our services.

We may collect personal data such as your name, email address, phone number, and browsing activity to improve your experience. This information is stored securely and is never sold to third parties.

We use cookies and similar technologies to personalize content and analyze user behavior. You may choose to disable cookies via your browser settings, though this may limit functionality.

You have the right to request access to your personal data or ask for it to be corrected or deleted. To make such a request, contact us through the information provided on our platform.
TEXT,
            ],
            [
                'type' => 'refund',
                'title' => 'Refund Policy',
                'text' => <<<TEXT
Refunds are subject to review and are granted based on specific eligibility criteria outlined below. If you believe you are entitled to a refund, please submit a request within 7 days of the original transaction.

Refunds may be issued if a booking is canceled by the host, the event is not delivered as promised, or there is a verified technical issue that prevented service access. All refund requests are investigated for authenticity and fairness.

Approved refunds will be processed within 5–10 business days, depending on your original payment method. Please note that service charges may be non-refundable in some cases.

We reserve the right to deny refund requests that do not meet the criteria stated. For additional clarification, feel free to contact our support team.
TEXT,
            ],
        ];

        foreach ($policies as $policy) {
            Policies::updateOrCreate(
                ['type' => $policy['type']],
                $policy
            );
        }
    }
}
