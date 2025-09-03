<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CmsPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('cms_pages')->truncate();
        
        // Define the pages
        $pages = [
            [
                'title' => 'Terms of Use',
                'slug' => 'terms-of-use',
                'content_html' => '
                    
<p><strong>Effective Date:</strong> 8/01/2025</p>

<p>Welcome to InvestorMaxx. These Terms and Conditions ("Terms") govern your access to and use of InvestorMaxx ("Site," "we," "us," or "our"), including any content, functionality, and services offered on or through the Site. By accessing or using this Site, you agree to be bound by these Terms. If you do not agree, do not access or use the Site.</p>

<h2>1. Use of the Site</h2>
<p>InvestorMaxx provides access to curated business data for research, marketing, and informational purposes. You may use the Site only for lawful purposes and in accordance with these Terms.</p>

<h2>2. Eligibility</h2>
<p>You must be at least 18 years old or the age of majority in your jurisdiction to use this Site.</p>

<h2>3. Intellectual Property</h2>
<p>All content, trademarks, logos, and data on this Site are the property of InvestorMaxx or its licensors. You may not reproduce, distribute, modify, or create derivative works without written permission.</p>

<h2>4. Data Use and Restrictions</h2>
<p>Users may not:</p>
<ul>
  <li>Use data from InvestorMaxx for unlawful purposes, including spam or harassment.</li>
  <li>Resell or republish our data without express written consent.</li>
  <li>Attempt to reverse-engineer or scrape our proprietary datasets.</li>
</ul>
<p>Users are solely responsible for ensuring their use of any data obtained from InvestorMaxx complies with all applicable laws and regulations, including but not limited to the Telephone Consumer Protection Act (TCPA), the CAN-SPAM Act, and all Do Not Call (DNC) rules enforced by the Federal Trade Commission (FTC) and the Federal Communications Commission (FCC). InvestorMaxx assumes no responsibility for any misuse or illegal use of its data by end users.</p>

<h2>5. Data Sourcing Compliance</h2>
<p>All business data provided through InvestorMaxx is collected solely from publicly accessible and legally permissible sources in accordance with applicable U.S. laws. InvestorMaxx does not knowingly collect or distribute data in violation of any applicable website`s terms of service or federal, state, or local laws. We make reasonable efforts to comply with all relevant laws, including but not limited to the Computer Fraud and Abuse Act (CFAA), CAN-SPAM Act, and applicable privacy regulations.We respect robots.txt directives and refrain from circumventing technical access controls.</p>

<h2>6. Accuracy of Information</h2>
<p>We strive for accuracy but do not guarantee that all data is current, complete, or error-free. Any reliance on the data is at the user`s own risk. We disclaim all liability arising from such use.</p>

<h2>7. Removal Requests</h2>
<p>If a business finds its information listed and wishes to request removal, they may contact us at <a href="mailto:info@investormaxx.io">info@investormaxx.io</a>. We review and evaluate all such requests in good faith and will remove data that cannot be lawfully displayed.</p>

<h2>8. Disclaimers</h2>
<p>The Site and all content are provided "as is" and "as available," without warranties of any kind. We disclaim all warranties, express or implied, including but not limited to merchantability, fitness for a particular purpose, and non-infringement. We do not guarantee uninterrupted access to the Site or that the Site will be free from errors or harmful components.</p>

<h2>9. Limitation of Liability</h2>
<p>To the fullest extent permitted by law, InvestorMaxx shall not be liable for any direct, indirect, incidental, special, or consequential damages, including but not limited to loss of revenue, profits, or data, arising out of or in connection with your access to or use of the Site or any data obtained therefrom. In the event of a claim or dispute, our total liability shall not exceed the amount paid, if any, by the user to access the Site.</p>

<h2>10. Indemnification</h2>
<p>You agree to indemnify, defend, and hold harmless InvestorMaxx, its owners, officers, directors, employees, and agents from and against any and all claims, demands, losses, liabilities, damages, judgments, costs, and expenses (including reasonable attorneys fees) arising out of or relating to your use of the Site, your violation of these Terms, or your violation of any rights of a third party.</p>

<h2>11. Legal Notice</h2>
<p>InvestorMaxx operates in accordance with U.S. federal and state law. All information presented is believed to be legally sourced and publicly accessible at the time of collection. If you believe your rights have been infringed, you agree to notify us at <a href="mailto:info@investormaxx.io">info@investormaxx.io</a> prior to initiating legal action, allowing us a reasonable opportunity to address the matter. Any legal claims must be brought in the state or federal courts located in Maricopa County, Arizona.</p>

<h2>12. Modifications to Terms</h2>
<p>We reserve the right to modify these Terms at any time. Updated versions will be posted on the Site and are effective upon posting.</p>

<h2>13. Governing Law</h2>
<p>These Terms are governed by and construed in accordance with the laws of the State of Arizona, without regard to its conflict of laws principles.</p>

<h2>14. Dispute Resolution</h2>
<p>Any dispute, claim, or controversy arising out of or relating to these Terms, your use of the Site, or the services provided shall be resolved exclusively through binding arbitration conducted in Maricopa County, Arizona, in accordance with the rules of the American Arbitration Association (AAA). By using the Site, you waive any right to a trial by jury or to participate in a class action. Judgment on the arbitration award may be entered in any court having jurisdiction.</p>

<h2>15. Chargebacks and Collections</h2>
<p>By using InvestorMaxx, you agree not to initiate chargebacks or payment disputes without first contacting us to attempt a resolution. In the event of an unauthorized chargeback, we reserve the right to:</p>
<ul>
  <li>Immediately suspend or terminate your access to InvestorMaxx and any associated services;</li>
  <li>Submit your account to a third-party collections agency for the outstanding balance plus any applicable collection fees;</li>
  <li>Report the delinquency to credit reporting agencies if permitted by law.</li>
</ul>
<p>All fees and payments are considered final and non-refundable unless otherwise stated in writing by InvestorMaxx.</p>

                ',
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content_html' => '
                    <p><strong>Effective Date:</strong> 8/01/2025</p>
                    <p>This Privacy Policy describes how InvestorMaxx collects, uses, and shares information when you use our Site.</p>

                    <h2>1. Information We Collect</h2>
                    <h3>Directly from Users</h3>
                    <p>Name, email address, and other contact information if voluntarily provided.</p>

                    <h3>Automatically Collected</h3>
                    <p>IP address, browser type, and usage data.</p>

                    <h3>From Third Parties</h3>
                    <p>We aggregate publicly available business information from legal and reputable sources.</p>

                    <h2>2. How We Use Information</h2>
                    <p>To operate and improve our services.</p>
                    <p>To respond to inquiries and support requests.</p>
                    <p>To enforce our Terms and legal obligations.</p>

                    <h2>3. Data Sharing</h2>
                    <p>We do not sell personal data of individuals.</p>
                    <p>We may share business data with customers and partners for lawful business purposes.</p>
                    <p>We may disclose information if required by law or in response to legal requests.</p>

                    <h2>4. Data Removal</h2>
                    <p>Businesses may request removal of their data by emailing <a href="mailto:info@investormaxx.io">info@investormaxx.io</a>.</p>
                    <p>We evaluate and honor valid requests as appropriate.</p>

                    <h2>5. Data Security</h2>
                    <p>We implement reasonable security measures to protect the information we collect from unauthorized access, disclosure, alteration, or destruction.</p>


                    <h2>6. SMS Policy</h2>
                    <p><strong>Notice:</strong> No mobile information will be shared with third parties/affiliates for marketing/promotional purposes. All the above categories exclude text messaging originator opt-in data and consent; this information will not be shared with any third parties.</p>

                    <h2>7. Removal Requests</h2>
                    <p>If a business finds its information listed and wishes to request removal, they may contact us at <a href="mailto:info@investormaxx.io">info@investormaxx.io</a>.</p>
                    <p>We review and evaluate all such requests in good faith and will remove data that cannot be lawfully displayed.</p>

                    <h2>Product Description</h2>
                    <p>By providing your mobile phone number, you consent to receive SMS messages from InvestorMaxx related to offers, account notifications, and similar communications.</p>

                    <h3>Message Frequency</h3>
                    <p>Message frequency may vary.</p>

                    <h3>Message and Data Rates</h3>
                    <p>Standard message and data rates may apply depending on your carrier.</p>

                    <h3>SMS Consent Disclosure</h3>
                    <p>By providing your number, you agree to receive communications via SMS from InvestorMaxx LLC. You may opt out by replying “STOP” or get help by replying “HELP.”</p>

                    <h3>Opting Out</h3>
                    <p>You may opt out at any time by replying “STOP” to any message. A confirmation will follow, and no further SMS will be sent.</p>

                    <h3>Help and Support</h3>
                    <p>For help, reply “HELP” to any SMS, or email our support team at <a href="mailto:info@investormaxx.com">info@investormaxx.com</a>.</p>

                    <h3>Privacy</h3>
                    <p>Your phone number will be handled in accordance with our Privacy Policy. We do not sell or share your phone number with third parties except as required by law.</p>',
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Fullfillment & Refund Policy',
                'slug' => 'fullfillment-refund-policy',
                'content_html' => '
                    <p><strong>Effective Date:</strong> 8/01/2025</p>

                    <p>At InvestorMaxx, we provide instant access to digital business data products upon successful payment. This Fulfillment Policy outlines how purchases are delivered and how we handle any fulfillment-related issues.</p>

                    <h2>1. Delivery Method</h2>
                    <p>All products sold on InvestorMaxx are digital. Once your payment is processed, your purchased data lists are <strong>immediately available</strong> under the “My Purchased Lists” section of your InvestorMaxx account. No physical products will be shipped.</p>

                    <h2>2. Delivery Timeline</h2>
                    <p>Access is granted instantly after checkout. If your list does not appear right away, please contact <a href="mailto:info@investormaxx.io">info@investormaxx.io</a> with your order ID.</p>

                    <h2>3. Refunds</h2>
                    <p>Due to the digital nature of our products and the instant access provided, <strong>all sales are final</strong>. Refunds will only be considered in cases of technical failure or duplicate billing, and must be requested within 7 days of purchase.</p>

                    <h2>4. Chargebacks</h2>
                    <p>Initiating a chargeback without contacting us first is a violation of our Terms. In such cases, we reserve the right to:</p>
                    <ul>
                    <li>Suspend or ban your account from using InvestorMaxx,</li>
                    <li>Submit your account to collections for the original amount plus fees,</li>
                    <li>Deny access to future purchases.</li>
                    </ul>

                    <h2>5. Contact</h2>
                    <p>If you experience any delivery issues, please email us at <a href="mailto:info@investormaxx.io">info@investormaxx.io</a>. We`re here to help.</p>
                    ',
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'HTML Demo',
                'slug' => 'html-demo',
                'content_html' => '
                    <h1>HTML Heading Tags Demo</h1>
                    <p>This page demonstrates all HTML heading tags from H1 to H6 and anchor tags.</p>
                    
                    <h2>H2 Heading Example</h2>
                    <p>This is a paragraph under an H2 heading.</p>
                    
                    <h3>H3 Heading Example</h3>
                    <p>This is a paragraph under an H3 heading.</p>
                    
                    <h4>H4 Heading Example</h4>
                    <p>This is a paragraph under an H4 heading.</p>
                    
                    <h5>H5 Heading Example</h5>
                    <p>This is a paragraph under an H5 heading.</p>
                    
                    <h6>H6 Heading Example</h6>
                    <p>This is a paragraph under an H6 heading.</p>
                    
                    <p>Below are examples of anchor tags:</p>
                    <ul>
                        <li><a href="https://example.com">External link example</a></li>
                        <li><a href="/terms-of-use">Internal link example</a></li>
                        <li><a href="mailto:info@example.com">Email link example</a></li>
                        <li><a href="tel:+18001234567">Phone link example</a></li>
                        <li><a href="#top">Anchor link example</a></li>
                        <li><a href="https://example.com" target="_blank">Link that opens in new tab</a></li>
                    </ul>
                ',
                'status' => 'draft',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
        
        // Insert the pages
        foreach ($pages as $page) {
            CmsPage::create($page);
        }
    }
}
