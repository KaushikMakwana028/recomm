<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Legal Pages Helper
 *
 * Provides shipped default legal texts, policy writing guidance,
 * and database resolution for Terms & Conditions, Privacy Policy, and Refund Policy.
 */

if (!function_exists('get_all_legal_page_slugs')) {
    /**
     * Get list of supported legal page slugs
     *
     * @return array
     */
    function get_all_legal_page_slugs()
    {
        return ['terms_conditions', 'privacy_policy', 'refund_policy'];
    }
}

if (!function_exists('get_legal_page_title')) {
    /**
     * Get default title for a legal page slug
     *
     * @param string $slug
     * @return string
     */
    function get_legal_page_title($slug)
    {
        $titles = [
            'terms_conditions' => 'Terms & Conditions',
            'privacy_policy'   => 'Privacy Policy',
            'refund_policy'    => 'Refund & Cancellation Policy',
        ];

        return isset($titles[$slug]) ? $titles[$slug] : ucwords(str_replace('_', ' ', $slug));
    }
}

if (!function_exists('get_default_legal_page')) {
    /**
     * Returns the built-in shipped default content for a legal page.
     * This content is baked into code/config and acts as the infallible fallback.
     *
     * @param string $slug
     * @return array ['title' => string, 'content' => string]
     */
    function get_default_legal_page($slug)
    {
        $slug = strtolower(trim($slug));

        switch ($slug) {
            case 'terms_conditions':
                return [
                    'title'   => 'Terms & Conditions',
                    'content' => <<<HTML
<h2>RECOMM MASTER TERMS &amp; CONDITIONS</h2>
<p class="text-muted"><strong>Effective Date:</strong> January 1, 2026 &nbsp;|&nbsp; <strong>Last Updated:</strong> September 15, 2026</p>

<h3>1. INTRODUCTION</h3>
<p>Welcome to <strong>ReComm</strong>, a technology-enabled hyperlocal commerce marketplace operated by <strong>ReComm Technologies Private Limited</strong> (&ldquo;ReComm&rdquo;, &ldquo;Company&rdquo;, &ldquo;we&rdquo;, &ldquo;us&rdquo; or &ldquo;our&rdquo;).</p>
<p>ReComm provides an online and technology-enabled platform through its website, mobile application and associated digital services through which customers may discover, compare and purchase products and/or services offered by participating vendors, retailers and other eligible sellers (&ldquo;Vendor(s)&rdquo;).</p>
<p>Depending upon the transaction and location, ReComm may also facilitate payment processing, order management, customer support, delivery coordination, logistics and other technology-enabled services through third-party service providers.</p>
<p>These Terms and Conditions (&ldquo;Terms&rdquo;) govern access to and use of the ReComm platform by customers, vendors and other authorised users.</p>
<p>By accessing, registering with, browsing or using ReComm, placing an order, registering as a Vendor, or otherwise using any ReComm service, you acknowledge that you have read, understood and agreed to these Terms and the policies incorporated into them.</p>
<p>If you do not agree with these Terms, you should not access or use ReComm.</p>

<h3>2. DEFINITIONS</h3>
<p>For purposes of these Terms:</p>
<ul>
    <li><strong>&ldquo;Customer&rdquo;</strong> means a person who accesses ReComm or purchases or attempts to purchase products/services through the Platform.</li>
    <li><strong>&ldquo;Vendor&rdquo; / &ldquo;Seller&rdquo;</strong> means a retailer, merchant, shop, business or other eligible person/entity authorised by ReComm to list and sell products or services through the Platform.</li>
    <li><strong>&ldquo;Platform&rdquo;</strong> means the ReComm website, mobile application, APIs, software, technology systems, dashboards and related digital interfaces.</li>
    <li><strong>&ldquo;Product&rdquo;</strong> means any goods or services listed by a Vendor through the Platform.</li>
    <li><strong>&ldquo;Order&rdquo;</strong> means a request submitted by a Customer to purchase one or more Products from one or more Vendors.</li>
    <li><strong>&ldquo;Delivery Partner&rdquo;</strong> means a delivery person, logistics provider or third-party delivery service engaged or enabled by ReComm or the Vendor.</li>
    <li><strong>&ldquo;Order Value&rdquo;</strong> means the applicable price of Products together with applicable delivery charges, taxes and other disclosed charges.</li>
    <li><strong>&ldquo;Payment Service Provider&rdquo;</strong> means an authorised third-party payment gateway, payment aggregator, banking institution, UPI provider or other payment service provider used for processing transactions.</li>
    <li><strong>&ldquo;Personal Data&rdquo;</strong> means information relating to an identified or identifiable individual and includes information protected under applicable data-protection law.</li>
</ul>

<h3>3. ELIGIBILITY</h3>
<p>The Platform may only be used by persons legally competent to enter into binding agreements under applicable Indian law.</p>
<p>Users shall provide accurate and current information during registration and shall maintain the accuracy of such information.</p>
<p>ReComm may suspend or terminate an account where information is materially inaccurate, misleading, fraudulent or incomplete, subject to applicable law.</p>

<h3>4. RECOMM AS A MARKETPLACE</h3>
<p>Unless expressly stated otherwise for a particular product or service, ReComm operates as a technology-enabled marketplace/facilitator connecting Customers with participating Vendors.</p>
<p>The Vendor is responsible for the legality, quality, safety, authenticity, description, pricing, packaging, inventory, invoicing, warranty and statutory compliance of Products listed by that Vendor, subject to applicable law.</p>
<p>A transaction may therefore involve contractual obligations between the Customer and Vendor, while ReComm may separately provide technology, payment facilitation, delivery coordination, customer-support or other services.</p>
<p>Nothing in these Terms excludes or limits any liability or consumer right that cannot lawfully be excluded or limited.</p>

<h3>5. PRODUCT INFORMATION</h3>
<p>Vendors must provide accurate and complete information regarding Products, including where applicable:</p>
<ul>
    <li>Product name;</li>
    <li>Brand;</li>
    <li>Variant;</li>
    <li>Quantity/pack size;</li>
    <li>MRP/RSP;</li>
    <li>Selling price;</li>
    <li>Applicable taxes;</li>
    <li>Product images;</li>
    <li>Country of origin;</li>
    <li>Ingredients or material information;</li>
    <li>Expiry/best-before information;</li>
    <li>Applicable licences or certifications;</li>
    <li>Warranty information;</li>
    <li>Return/refund eligibility; and</li>
    <li>Delivery restrictions.</li>
</ul>
<p>Product descriptions and images must reasonably correspond to the Product actually supplied.</p>
<p>ReComm may remove, restrict, suspend or modify a listing where it reasonably believes that the listing violates applicable law, these Terms or ReComm policies.</p>

<h3>6. PRICING</h3>
<p>Prices displayed on the Platform shall be the prices applicable to the Customer at the time of order placement, subject to clearly disclosed taxes, delivery charges, platform/service charges, discounts and other applicable charges.</p>
<p>A Vendor shall not intentionally provide misleading prices, manipulate MRP/RSP information or otherwise engage in an unfair pricing practice.</p>
<p>Where an obvious technical or typographical pricing error occurs, ReComm may take appropriate corrective action consistent with applicable law.</p>

<h3>7. ORDER PLACEMENT AND ACCEPTANCE</h3>
<p>Placing an Order constitutes an offer by the Customer to purchase the selected Products.</p>
<p>An automated order confirmation does not necessarily constitute final acceptance of the Order by the Vendor where the Vendor or ReComm's system is required to confirm inventory, serviceability or other transaction conditions.</p>
<p>Orders may be cancelled or rejected where:</p>
<ul>
    <li>the Product is unavailable;</li>
    <li>the delivery location is outside the serviceable area;</li>
    <li>the Product is prohibited or restricted;</li>
    <li>there is a suspected fraudulent transaction;</li>
    <li>payment cannot be successfully authorised;</li>
    <li>the Vendor is unable to fulfil the Order; or</li>
    <li>required by applicable law or legitimate platform-security requirements.</li>
</ul>
<p>Where an Order is cancelled after payment, any eligible refund shall be processed according to the applicable Refund &amp; Cancellation Policy.</p>

<h3>8. INVENTORY AND AVAILABILITY</h3>
<p>Vendors are responsible for maintaining accurate inventory information.</p>
<p>A Vendor should promptly update inventory where a Product becomes unavailable.</p>
<p>ReComm may temporarily disable Products showing repeated stock discrepancies, excessive cancellations or fulfilment failures.</p>

<h3>9. DELIVERY</h3>
<p>Depending on the transaction, delivery may be:</p>
<p><strong>A. Vendor Self-Delivery</strong><br>The Vendor is responsible for preparing and delivering the Order.</p>
<p><strong>B. ReComm/Delivery Partner Delivery</strong><br>A delivery partner may collect the Order from the Vendor and deliver it to the Customer.</p>
<p>Available delivery options may include:</p>
<ul>
    <li>Immediate;</li>
    <li>Later / After a specified period;</li>
    <li>Lunch delivery;</li>
    <li>Dinner delivery; or</li>
    <li>Custom scheduled delivery.</li>
</ul>
<p>Estimated delivery times are estimates and may change due to traffic, weather, order volume, Vendor preparation time, distance, serviceability, force majeure or other circumstances beyond reasonable control.</p>

<h3>10. CUSTOMER RESPONSIBILITIES DURING DELIVERY</h3>
<p>The Customer shall provide an accurate delivery address and contact information and shall remain reasonably available to receive the Order.</p>
<p>Where permitted by the applicable delivery process, proof of delivery may include OTP verification, digital confirmation, photograph, electronic signature or other reasonable verification.</p>
<p>A Customer should inspect Products promptly after delivery and report shortages, damage, incorrect Products or other eligible issues within the applicable reporting period.</p>

<h3>11. PAYMENTS</h3>
<p>ReComm may make available payment methods including, where supported:</p>
<ul>
    <li>UPI;</li>
    <li>Credit/debit cards;</li>
    <li>Net banking;</li>
    <li>Wallets;</li>
    <li>Cash on Delivery;</li>
    <li>QR-based payment; and</li>
    <li>Other authorised payment methods.</li>
</ul>
<p>Payments may be processed by third-party payment service providers.</p>
<p>ReComm does not store complete payment-card credentials where such information is handled directly by an authorised payment service provider, except to the extent permitted or required by applicable law and technical arrangements.</p>
<p>A transaction may be placed on hold, cancelled or subjected to additional verification where ReComm reasonably suspects fraud, misuse, payment failure or unlawful activity.</p>

<h3>12. REFUNDS</h3>
<p>Refund eligibility shall depend upon the nature of the Product, reason for cancellation/return, payment method, Vendor policy and applicable law.</p>
<p>Eligible refunds may arise from:</p>
<ul>
    <li>cancelled Orders;</li>
    <li>unavailable Products;</li>
    <li>duplicate payment;</li>
    <li>incorrect Product;</li>
    <li>damaged Product;</li>
    <li>eligible defective Product;</li>
    <li>eligible return;</li>
    <li>failed delivery; or</li>
    <li>other circumstances recognised under the applicable policy.</li>
</ul>
<p>Refunds shall be processed through the applicable payment mechanism, subject to applicable banking/payment timelines, in accordance with our standalone <strong>Refund &amp; Cancellation Policy</strong>.</p>

<h3>13. RETURNS, REPLACEMENTS AND REFUNDS</h3>
<p>The return, replacement and refund eligibility of each Product shall be displayed or otherwise communicated where required.</p>
<p>Certain Products may be non-returnable due to their nature, including certain perishable, hygiene-sensitive, personalised or legally restricted Products, subject to applicable law.</p>
<p>Nothing in this clause shall restrict a Customer's statutory rights in relation to defective, deficient, unsafe, spurious or misrepresented Products.</p>

<h3>14. VENDOR RESPONSIBILITIES</h3>
<p>Every Vendor shall:</p>
<ol>
    <li>maintain all registrations, licences and approvals required for its business;</li>
    <li>provide accurate Product information;</li>
    <li>maintain sufficient inventory;</li>
    <li>fulfil accepted Orders within the committed timeline;</li>
    <li>comply with applicable tax requirements;</li>
    <li>comply with applicable food, consumer, legal-metrology, packaging and product-safety requirements;</li>
    <li>issue legally compliant invoices where applicable;</li>
    <li>ensure Products are genuine and lawful;</li>
    <li>comply with applicable MRP/RSP requirements;</li>
    <li>maintain appropriate packaging;</li>
    <li>honour applicable warranties and guarantees;</li>
    <li>comply with return/refund obligations;</li>
    <li>protect Customer information;</li>
    <li>not manipulate ratings or reviews; and</li>
    <li>not engage in fraudulent, deceptive or unfair trade practices.</li>
</ol>

<h3>15. PROHIBITED PRODUCTS</h3>
<p>Vendors shall not list or sell Products prohibited under applicable law or ReComm policy.</p>
<p>ReComm may maintain a prohibited/restricted-product list and may update that list from time to time.</p>
<p>Products may include additional restrictions based on category, location, licence requirements, age restrictions, safety requirements or government regulations.</p>

<h3>16. CUSTOMER CONDUCT</h3>
<p>Customers shall not:</p>
<ul>
    <li>create fraudulent accounts;</li>
    <li>use another person's account without authorisation;</li>
    <li>abuse promotional offers;</li>
    <li>submit false claims;</li>
    <li>manipulate ratings or reviews;</li>
    <li>misuse refunds or chargebacks;</li>
    <li>attempt unauthorised access;</li>
    <li>interfere with Platform security;</li>
    <li>upload malicious software;</li>
    <li>use the Platform for unlawful purposes; or</li>
    <li>otherwise misuse ReComm services.</li>
</ul>

<h3>17. REVIEWS AND RATINGS</h3>
<p>Customers may provide genuine reviews and ratings based on their actual experience.</p>
<p>Reviews must not contain unlawful, defamatory, threatening, hateful, obscene or misleading content.</p>
<p>ReComm may moderate, remove or restrict content that violates applicable law or Platform policy.</p>
<p>No person may artificially manipulate ratings, reviews or rankings.</p>

<h3>18. PROMOTIONS, COUPONS AND OFFERS</h3>
<p>Promotions, discounts, coupons and cashback offers may be subject to additional conditions.</p>
<p>ReComm may specify eligibility, validity period, maximum benefit, minimum order value, geographic restrictions and other conditions.</p>
<p>Unless expressly stated otherwise, promotional benefits have no cash value and cannot be transferred.</p>
<p>ReComm may cancel or restrict benefits obtained through fraud, misuse or violation of promotional conditions.</p>

<h3>19. CUSTOMER DATA AND PRIVACY</h3>
<p>ReComm shall collect and process personal information in accordance with its <strong>Privacy Policy</strong> and applicable data-protection laws.</p>
<p>Customer information shall only be made available to Vendors or Delivery Partners to the extent reasonably necessary for fulfilment, support, delivery, payment, legal compliance or other permitted purposes.</p>
<p>Vendors and Delivery Partners shall use Customer information only for authorised purposes and shall implement appropriate safeguards against unauthorised access, disclosure, copying or misuse.</p>

<h3>20. ACCOUNT SECURITY</h3>
<p>Users are responsible for maintaining the confidentiality of their login credentials and OTPs.</p>
<p>Users must immediately notify ReComm of suspected unauthorised access or account compromise.</p>
<p>Users shall not share OTPs or authentication credentials with unauthorised persons.</p>

<h3>21. INTELLECTUAL PROPERTY</h3>
<p>All ReComm software, trademarks, logos, designs, interfaces, databases, graphics, text, technology and other proprietary materials are owned by or licensed to ReComm unless expressly stated otherwise.</p>
<p>Users receive only a limited, non-exclusive, non-transferable and revocable right to use the Platform for its intended lawful purpose.</p>
<p>No User may copy, reproduce, reverse engineer, scrape, modify, distribute or commercially exploit ReComm's proprietary technology without prior written permission, except where expressly permitted by applicable law.</p>

<h3>22. USER-GENERATED CONTENT</h3>
<p>Where a User submits photographs, reviews, comments or other content to ReComm, the User represents that they have the necessary rights to submit such content.</p>
<p>The User grants ReComm a non-exclusive, royalty-free licence to host, reproduce, display and use such content for operating, improving and promoting the Platform, subject to applicable law and the Privacy Policy.</p>

<h3>23. THIRD-PARTY SERVICES</h3>
<p>ReComm may integrate third-party payment gateways, logistics providers, mapping services, communication services, cloud services and other technology providers.</p>
<p>Third-party services may be governed by additional terms and privacy policies.</p>
<p>ReComm shall not be responsible for matters solely attributable to an independent third-party service provider, subject always to applicable law.</p>

<h3>24. PLATFORM AVAILABILITY</h3>
<p>ReComm will use reasonable efforts to maintain Platform availability.</p>
<p>However, temporary interruptions may occur because of:</p>
<ul>
    <li>maintenance;</li>
    <li>software updates;</li>
    <li>network failures;</li>
    <li>cybersecurity incidents;</li>
    <li>infrastructure failures;</li>
    <li>payment-provider outages;</li>
    <li>telecommunications failures;</li>
    <li>government restrictions; or</li>
    <li>other circumstances beyond reasonable control.</li>
</ul>

<h3>25. FRAUD AND SECURITY</h3>
<p>ReComm may monitor transactions and account activity for legitimate security, fraud-prevention, regulatory and operational purposes.</p>
<p>Where ReComm reasonably suspects fraud, abuse, unauthorised activity or unlawful conduct, it may suspend an account, hold a transaction or payment, restrict access or take other appropriate action subject to applicable law.</p>

<h3>26. ACCOUNT SUSPENSION AND TERMINATION</h3>
<p>ReComm may suspend or terminate an account where permitted by law, including for:</p>
<ul>
    <li>material breach of these Terms;</li>
    <li>fraudulent activity;</li>
    <li>repeated misuse;</li>
    <li>unlawful activity;</li>
    <li>payment abuse;</li>
    <li>false information;</li>
    <li>repeated fulfilment failures by Vendors;</li>
    <li>sale of prohibited Products;</li>
    <li>security threats; or</li>
    <li>conduct materially harmful to Customers, Vendors or ReComm.</li>
</ul>
<p>Where appropriate, ReComm may provide notice and an opportunity to remedy the breach.</p>
<p>Termination shall not affect obligations that accrued before termination.</p>

<h3>27. DISCLAIMERS</h3>
<p>Except where expressly provided or required by applicable law, ReComm does not guarantee that:</p>
<ul>
    <li>every Product will always be available;</li>
    <li>every Vendor will fulfil every Order;</li>
    <li>delivery will always occur within an estimated time;</li>
    <li>the Platform will remain uninterrupted or error-free; or</li>
    <li>third-party services will always be available.</li>
</ul>
<p>Nothing in these Terms excludes any statutory guarantee, consumer right or liability that cannot legally be excluded.</p>

<h3>28. LIMITATION OF LIABILITY</h3>
<p>To the maximum extent permitted by applicable law, ReComm shall not be liable for indirect, incidental, special or consequential losses arising solely from circumstances outside ReComm's reasonable control.</p>
<p>Nothing in these Terms shall exclude or restrict liability to the extent such exclusion or restriction is prohibited by applicable law, including liability that cannot legally be excluded for fraud, wilful misconduct, death or personal injury caused by negligence, or statutory consumer rights.</p>

<h3>29. INDEMNIFICATION</h3>
<p>To the extent permitted by law, a Vendor shall indemnify ReComm against claims, losses, penalties and reasonable costs arising from:</p>
<ul>
    <li>unlawful Products;</li>
    <li>inaccurate Product information;</li>
    <li>Vendor's breach of applicable law;</li>
    <li>infringement of third-party intellectual property;</li>
    <li>Vendor's breach of these Terms;</li>
    <li>fraudulent conduct; or</li>
    <li>misuse of Customer information by the Vendor.</li>
</ul>
<p>The scope of indemnification shall be subject to applicable law.</p>

<h3>30. FORCE MAJEURE</h3>
<p>Neither party shall be responsible for failure or delay caused by circumstances beyond reasonable control, including natural disasters, floods, fire, epidemic/pandemic, war, civil unrest, government restrictions, major infrastructure failures, telecommunications outages, strikes or widespread technology failures.</p>

<h3>31. GRIEVANCE REDRESSAL</h3>
<p>ReComm shall maintain a grievance redressal mechanism in accordance with applicable law.</p>
<table class="table table-bordered table-sm my-3" style="max-width: 600px;">
    <tbody>
        <tr><td><strong>Grievance Officer:</strong></td><td>Mr. Ramesh Patel</td></tr>
        <tr><td><strong>Designation:</strong></td><td>Head of Compliance &amp; Customer Grievance</td></tr>
        <tr><td><strong>Email:</strong></td><td>grievance@recomm.in</td></tr>
        <tr><td><strong>Phone:</strong></td><td>+91 (0261) 234-5678</td></tr>
        <tr><td><strong>Registered Address:</strong></td><td>ReComm Technologies Pvt. Ltd., 402 Silicon Square, Ring Road, Surat, Gujarat - 395002</td></tr>
    </tbody>
</table>
<p>Complaints should include sufficient information to identify the User, Order and nature of the grievance.</p>
<p>ReComm shall acknowledge and process grievances in accordance with applicable law and its grievance-redressal procedure. Grievances are acknowledged within 48 hours and resolved within one month of receipt.</p>

<h3>32. GOVERNING LAW</h3>
<p>These Terms shall be governed by the laws of India.</p>
<p>Nothing in these Terms shall prevent a consumer from exercising a statutory right to approach a competent consumer authority, court, tribunal or other forum available under applicable law. Subject to mandatory statutory consumer dispute forums, courts situated in Surat, Gujarat shall have exclusive jurisdiction over legal disputes arising under these Terms.</p>

<h3>33. DISPUTE RESOLUTION</h3>
<p>ReComm encourages Users to first attempt resolution through its customer-support and grievance-redressal mechanism.</p>
<p>Where a dispute cannot be resolved through the applicable internal mechanism, the parties may pursue remedies available under applicable Indian law.</p>
<p>Any arbitration clause, if adopted for a particular commercial/vendor agreement, shall be separately drafted to comply with applicable law and shall not unlawfully restrict consumer remedies.</p>

<h3>34. CHANGES TO THESE TERMS</h3>
<p>ReComm may update these Terms from time to time to reflect changes in the Platform, business model, technology, applicable law or regulatory requirements.</p>
<p>Where required by law, ReComm shall provide appropriate notice of material changes.</p>
<p>Continued use of the Platform after the effective date of revised Terms may constitute acceptance to the extent permitted by applicable law.</p>

<h3>35. SEVERABILITY</h3>
<p>If any provision of these Terms is determined to be invalid or unenforceable, the remaining provisions shall continue to the extent permitted by law.</p>

<h3>36. ENTIRE AGREEMENT</h3>
<p>These Terms, together with the Privacy Policy, Refund &amp; Cancellation Policy, Vendor Agreement, Delivery Policy, Payment Policy and other policies expressly incorporated into these Terms, constitute the applicable contractual framework governing use of ReComm.</p>

<h3>37. CONTACT</h3>
<p><strong>ReComm</strong><br>
Legal Entity: <strong>ReComm Technologies Private Limited</strong><br>
Registered Office: <strong>402 Silicon Square, Ring Road, Surat, Gujarat - 395002</strong><br>
Customer Support: <strong>support@recomm.in</strong><br>
Vendor Support: <strong>vendor-support@recomm.in</strong><br>
Grievance Officer: <strong>Mr. Ramesh Patel &ndash; grievance@recomm.in &ndash; +91 (0261) 234-5678</strong><br>
Website: <strong>www.recomm.in</strong></p>
<p class="text-muted"><strong>Last Updated:</strong> September 15, 2026</p>
HTML
                ];

            case 'privacy_policy':
                return [
                    'title'   => 'Privacy Policy',
                    'content' => <<<HTML
<h2>RECOMM PRIVACY POLICY</h2>
<p class="text-muted"><strong>Effective Date:</strong> January 1, 2026 &nbsp;|&nbsp; <strong>Last Updated:</strong> September 15, 2026</p>

<h3>1. INTRODUCTION</h3>
<p>ReComm Technologies Private Limited (&ldquo;ReComm&rdquo;, &ldquo;Company&rdquo;, &ldquo;we&rdquo;, &ldquo;us&rdquo; or &ldquo;our&rdquo;) respects your privacy and is committed to protecting the Personal Data of Customers, Vendors and other Users of the ReComm website, mobile application, vendor portal and related digital services (collectively, the &ldquo;Platform&rdquo;).</p>
<p>This Privacy Policy is incorporated into, and should be read together with, our <strong>Master Terms &amp; Conditions</strong>. It explains what Personal Data we collect, why we collect it, how it is used and shared, how long it is retained, and the rights available to you.</p>
<p>This policy is framed in accordance with the <strong>Digital Personal Data Protection Act, 2023 (&ldquo;DPDP Act&rdquo;)</strong>, the <strong>Information Technology Act, 2000</strong> and the rules made thereunder, and other applicable Indian data-protection law.</p>
<p>By accessing or using the Platform, you consent to the collection, use, storage, disclosure and processing of your Personal Data as described in this Privacy Policy.</p>

<h3>2. WHO THIS POLICY APPLIES TO</h3>
<p>This Policy applies to:</p>
<ul>
    <li>Customers who browse, register on, or place Orders through the Platform;</li>
    <li>Vendors and their authorised staff who use the vendor portal or dashboard;</li>
    <li>Delivery Partners who use ReComm's logistics tools; and</li>
    <li>Any other individual whose Personal Data we process in connection with operating the Platform.</li>
</ul>

<h3>3. PERSONAL DATA WE COLLECT</h3>
<p>Depending on how you interact with the Platform, we may collect the following categories of Personal Data:</p>
<ul>
    <li><strong>Identity &amp; Contact Data:</strong> full name, mobile number, email address, profile display name, and Google/OAuth identifier where social sign-in is used.</li>
    <li><strong>Authentication Data:</strong> One-Time Password (OTP) verification tokens and login/session identifiers.</li>
    <li><strong>Address &amp; Location Data:</strong> saved delivery addresses (address line 1/2, landmark, city, state, pincode), geocoded pin coordinates, and precise device GPS location (collected only with your permission) used to identify nearby Vendors, confirm delivery serviceability, calculate distance-based delivery charges, and estimate delivery windows.</li>
    <li><strong>Order &amp; Transaction Data:</strong> Products purchased, quantities and variants, selected Vendor, chosen delivery slot, order notes, payment mode (Cash on Delivery or online), payment reference/transaction IDs, invoices and order history.</li>
    <li><strong>Payment Data:</strong> limited payment metadata (e.g. payment method, last four digits of a card, UPI handle) required for reconciliation. Full card numbers, CVVs and UPI PINs are handled solely by our authorised Payment Service Providers and are never stored by ReComm.</li>
    <li><strong>Vendor &amp; Delivery Partner Data:</strong> business/KYC details, bank or payout details, catalogue and inventory data, and performance metrics, where you register as a Vendor or Delivery Partner.</li>
    <li><strong>Device &amp; Usage Data:</strong> IP address, device identifiers, operating system, browser type, app version, crash/error logs, and interaction data collected for security, analytics and service improvement.</li>
    <li><strong>Communications:</strong> support tickets, chat messages, call recordings (where disclosed), reviews and ratings you submit.</li>
</ul>

<h3>4. HOW WE COLLECT PERSONAL DATA</h3>
<p>We collect Personal Data directly from you (registration, checkout, support requests), automatically through your use of the Platform (device/usage data, location), and from third parties such as Payment Service Providers, Delivery Partners and, where you choose to sign in through them, identity providers.</p>

<h3>5. HOW WE USE YOUR PERSONAL DATA</h3>
<p>We process Personal Data only for legitimate, disclosed purposes, including to:</p>
<ol>
    <li>create and manage your account and verify your identity via OTP;</li>
    <li>match your location with nearby Vendors and confirm delivery serviceability;</li>
    <li>calculate distance-based delivery charges and realistic delivery windows;</li>
    <li>process, confirm, pack and dispatch Orders, and coordinate handoff to Delivery Partners;</li>
    <li>send transactional communications such as order confirmations, delivery updates, OTPs and support responses;</li>
    <li>process payments and refunds through authorised Payment Service Providers;</li>
    <li>generate tax-compliant invoices and maintain accounting records;</li>
    <li>respond to support requests and resolve complaints or grievances;</li>
    <li>detect and prevent fraud, abuse, unauthorised access and duplicate accounts;</li>
    <li>improve, secure and personalise the Platform; and</li>
    <li>comply with applicable law, regulatory requirements and lawful requests from authorities.</li>
</ol>

<h3>6. LEGAL BASIS FOR PROCESSING</h3>
<p>We process your Personal Data on the basis of your consent, for the performance of a contract with you (fulfilling your Order), to comply with a legal obligation (such as tax or accounting law), or for our legitimate interests in operating, securing and improving the Platform, in each case as permitted under the DPDP Act and applicable law.</p>

<h3>7. SHARING AND DISCLOSURE OF PERSONAL DATA</h3>
<p>We do <strong>not</strong> sell or rent your Personal Data. We share Personal Data only to the extent reasonably necessary, including with:</p>
<ul>
    <li><strong>Vendors:</strong> the Vendor fulfilling your Order receives your name, ordered items, delivery address, phone number and order notes solely to pack, verify and dispatch the Order.</li>
    <li><strong>Delivery Partners:</strong> the assigned Delivery Partner receives your delivery address, coordinates and mobile number solely to navigate to and deliver your Order.</li>
    <li><strong>Payment Service Providers:</strong> authorised, RBI-regulated payment gateways and banking partners process your payment directly; ReComm does not receive or store full card numbers, CVVs or UPI PINs.</li>
    <li><strong>Service Providers:</strong> cloud hosting, SMS/communication, analytics and customer-support tool providers who process data on our behalf under contractual confidentiality obligations.</li>
    <li><strong>Legal &amp; Regulatory Authorities:</strong> where required by law, court order, or a lawful request from a government or law-enforcement authority.</li>
    <li><strong>Business Transfers:</strong> in connection with a merger, acquisition, financing or sale of assets, subject to equivalent privacy protections.</li>
</ul>

<h3>8. COOKIES AND SIMILAR TECHNOLOGIES</h3>
<p>We use session tokens, local storage and essential functional cookies to keep you signed in, remember your selected delivery address, persist your cart, and measure Platform performance. You may control cookies through your browser or device settings; disabling certain cookies may limit some Platform functionality.</p>

<h3>9. DATA RETENTION</h3>
<p>We retain Personal Data only for as long as necessary to fulfil the purposes described in this Policy, including to provide the Platform, resolve disputes, enforce our agreements, and meet accounting, taxation and other statutory record-keeping obligations, after which it is securely deleted or anonymised, subject to any longer retention period required by law.</p>

<h3>10. DATA STORAGE AND SECURITY</h3>
<p>Personal Data is stored on secure cloud servers located within India. We apply industry-standard safeguards, including encryption in transit (HTTPS/TLS) and at rest, role-based access controls, and periodic security review, to protect Personal Data against unauthorised access, alteration, disclosure or destruction. No method of transmission or storage is completely secure, and we encourage you to also safeguard your own credentials and OTPs.</p>

<h3>11. YOUR RIGHTS</h3>
<p>Subject to applicable law, including the DPDP Act, you have the right to:</p>
<ul>
    <li>access and review the Personal Data we hold about you;</li>
    <li>correct or update inaccurate or incomplete Personal Data, including your saved addresses and profile;</li>
    <li>withdraw consent for location access at any time through your device settings (you may then need to enter your address or pincode manually);</li>
    <li>request erasure of your account and associated Personal Data, subject to our ongoing legal, regulatory and accounting obligations;</li>
    <li>nominate another individual to exercise your rights in the event of death or incapacity, where enabled; and</li>
    <li>lodge a grievance with our Grievance Officer, and thereafter with the Data Protection Board of India or other competent authority, if unsatisfied with our response.</li>
</ul>

<h3>12. CHILDREN'S PRIVACY</h3>
<p>The Platform is not directed at children. Persons under 18 years of age may use the Platform only under the supervision of, and with consent from, a parent or legal guardian, in accordance with our Terms &amp; Conditions.</p>

<h3>13. THIRD-PARTY LINKS AND SERVICES</h3>
<p>The Platform may contain links to, or integrate with, third-party websites, payment gateways and services. This Privacy Policy does not apply to those third parties, and we encourage you to review their respective privacy policies.</p>

<h3>14. DATA BREACH NOTIFICATION</h3>
<p>In the event of a Personal Data breach that is likely to result in harm to affected individuals, we will notify affected Users and the relevant authorities in accordance with applicable law.</p>

<h3>15. CHANGES TO THIS POLICY</h3>
<p>We may update this Privacy Policy from time to time to reflect changes in our practices, technology, legal or regulatory requirements. Material changes will be notified through the Platform, and continued use after the effective date of the revised Policy constitutes acceptance to the extent permitted by applicable law.</p>

<h3>16. GRIEVANCE OFFICER / CONTACT</h3>
<p>For questions, concerns, or to exercise your rights under this Privacy Policy, please contact:</p>
<table class="table table-bordered table-sm my-3" style="max-width: 600px;">
    <tbody>
        <tr><td><strong>Grievance Officer:</strong></td><td>Mr. Ramesh Patel</td></tr>
        <tr><td><strong>Designation:</strong></td><td>Head of Compliance &amp; Customer Grievance</td></tr>
        <tr><td><strong>Email:</strong></td><td>privacy@recomm.in</td></tr>
        <tr><td><strong>Helpline:</strong></td><td>+91 (0261) 234-5678</td></tr>
        <tr><td><strong>Registered Address:</strong></td><td>ReComm Technologies Pvt. Ltd., 402 Silicon Square, Ring Road, Surat, Gujarat - 395002</td></tr>
    </tbody>
</table>
HTML
                ];

            case 'refund_policy':
                return [
                    'title'   => 'Refund & Cancellation Policy',
                    'content' => <<<HTML
<h2>RECOMM REFUND &amp; CANCELLATION POLICY</h2>
<p class="text-muted"><strong>Effective Date:</strong> January 1, 2026 &nbsp;|&nbsp; <strong>Last Updated:</strong> September 15, 2026</p>

<h3>1. OVERVIEW</h3>
<p>This Refund &amp; Cancellation Policy is incorporated into, and should be read together with, ReComm's <strong>Master Terms &amp; Conditions</strong>. Because Orders on ReComm are fulfilled by independent local Vendors and frequently involve perishable or time-sensitive goods, this Policy sets out when an Order may be cancelled, returned or refunded, and how refunds are processed.</p>

<h3>2. CANCELLATION BY THE CUSTOMER</h3>
<ul>
    <li><strong>Before the Vendor accepts the Order:</strong> you may cancel free of charge while the Order status remains &ldquo;Placed&rdquo; or &ldquo;Pending&rdquo;.</li>
    <li><strong>After the Vendor begins preparation or packing:</strong> cancellation through the app may no longer be possible. If you refuse delivery at the doorstep after this stage, applicable return-freight or restocking charges may be deducted from your refund.</li>
</ul>

<h3>3. CANCELLATION BY THE VENDOR OR RECOMM</h3>
<p>An Order may be cancelled by the Vendor or by ReComm's system where:</p>
<ul>
    <li>a Product is out of stock or fails a quality check at the Vendor's store;</li>
    <li>the Vendor experiences an unplanned closure or emergency holiday;</li>
    <li>the delivery address falls outside the Vendor's serviceable radius;</li>
    <li>payment cannot be authorised or a fraud check fails; or</li>
    <li>extreme weather, traffic disruption or a force-majeure event prevents fulfilment.</li>
</ul>
<p>Where an Order is cancelled by the Vendor or ReComm, a <strong>100% refund</strong> of the Order Value, including delivery and packaging charges, is issued.</p>

<h3>4. ELIGIBILITY FOR RETURNS AND REFUNDS</h3>
<p>You may request a replacement or a full/partial refund where:</p>
<ol>
    <li><strong>Damaged or tampered goods</strong> are delivered (broken seals, crushed packaging, visible spoilage);</li>
    <li><strong>Expired items</strong> are delivered past their printed &ldquo;Best Before&rdquo; or expiry date;</li>
    <li><strong>Items are missing</strong> from the delivered parcel despite being charged on the invoice;</li>
    <li>an <strong>incorrect Product</strong>, brand, variant or pack size is delivered; or</li>
    <li>the Product is otherwise <strong>defective or materially not as described</strong>, without prejudice to any statutory right you may have that cannot lawfully be excluded.</li>
</ol>

<h3>5. REPORTING WINDOWS AND VERIFICATION</h3>
<table class="table table-bordered table-sm my-3" style="max-width: 650px;">
    <thead class="table-light">
        <tr><th>Product Category</th><th>Reporting Window</th><th>Proof Required</th></tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Fresh Perishables</strong> (dairy, bread, fruits, vegetables)</td>
            <td>Within <strong>24 hours</strong> of delivery</td>
            <td>Clear photo of the item and delivery/order tag</td>
        </tr>
        <tr>
            <td><strong>Packaged Groceries &amp; Staples</strong> (grains, oils, FMCG)</td>
            <td>Within <strong>48 hours</strong> of delivery</td>
            <td>Photo of the packaging, invoice, and expiry seal</td>
        </tr>
        <tr>
            <td><strong>Non-food / general merchandise</strong></td>
            <td>Within <strong>48 hours</strong> of delivery</td>
            <td>Photo/video of the item and invoice</td>
        </tr>
    </tbody>
</table>
<p>To raise a claim, contact Customer Support via the ReComm app or <a href="mailto:support@recomm.in">support@recomm.in</a> with your Order ID and supporting photos. Claims are typically reviewed within 24&ndash;48 hours.</p>

<h3>6. NON-RETURNABLE AND NON-REFUNDABLE ITEMS</h3>
<p>Unless defective, spoiled on first opening, or not as described, the following are not eligible for return:</p>
<ul>
    <li>perishable items reported after the applicable reporting window;</li>
    <li>opened or partially consumed food items;</li>
    <li>personal-care, intimate-hygiene or cosmetic items with a broken protective seal;</li>
    <li>items damaged due to customer mishandling, inadequate refrigeration or improper storage after delivery; and</li>
    <li>Products expressly marked as non-returnable at the time of purchase.</li>
</ul>

<h3>7. REFUND MODES AND TIMELINES</h3>
<p>Once a refund is approved, funds are returned via the original payment method wherever possible:</p>
<ul>
    <li><strong>Online payments (UPI, card, net banking):</strong> credited to the original payment source within <strong>3&ndash;7 business days</strong>, subject to your bank's or Payment Service Provider's processing timelines.</li>
    <li><strong>Cash on Delivery (COD) Orders:</strong> refunded via UPI or bank transfer (NEFT/IMPS) to a verified account within <strong>2&ndash;4 business days</strong>, or credited instantly as ReComm Wallet/store credit with your consent.</li>
    <li><strong>Delivery charges:</strong> fully refunded where an entire Order is rejected, cancelled before dispatch, or cancelled by the Vendor/ReComm. Delivery charges are not refunded for partial-item returns from an otherwise-fulfilled Order.</li>
</ul>

<h3>8. PARTIAL REFUNDS</h3>
<p>Where only some items in an Order are eligible for return (e.g. one missing or damaged item among several), the refund is calculated based on the price of the affected item(s) only, and delivery charges are apportioned only where the entire Order is affected.</p>

<h3>9. DISPUTED OR REJECTED CLAIMS</h3>
<p>Where a claim cannot be verified from the evidence provided, or falls outside the eligibility criteria in this Policy, ReComm may decline the refund and will explain the reason. You may escalate a rejected claim to our Grievance Officer for review.</p>

<h3>10. CHANGES TO THIS POLICY</h3>
<p>ReComm may update this Refund &amp; Cancellation Policy from time to time to reflect operational or regulatory changes. The &ldquo;Last Updated&rdquo; date above indicates the most recent revision, and continued use of the Platform after that date constitutes acceptance to the extent permitted by applicable law.</p>

<h3>11. CUSTOMER SUPPORT AND ESCALATION</h3>
<p>For refund or cancellation queries, or to escalate an unresolved issue, please contact:</p>
<p><strong>Customer Support:</strong> <a href="mailto:support@recomm.in">support@recomm.in</a><br>
<strong>Helpline:</strong> +91 (0261) 234-5678 (8:00 AM &ndash; 10:00 PM IST)<br>
<strong>Escalations / Grievance Officer:</strong> <a href="mailto:grievance@recomm.in">grievance@recomm.in</a></p>
HTML
                ];

            default:
                return [
                    'title'   => ucwords(str_replace('_', ' ', $slug)),
                    'content' => "<p>Policy content for " . htmlspecialchars($slug) . " is currently being drafted.</p>"
                ];
        }
    }
}

if (!function_exists('get_legal_page_guidance')) {
    /**
     * Returns editor guidance and legal checklists specific to each policy type.
     *
     * @param string $slug
     * @return array ['summary' => string, 'checklist' => array, 'statutory' => string]
     */
    function get_legal_page_guidance($slug)
    {
        $slug = strtolower(trim($slug));

        switch ($slug) {
            case 'terms_conditions':
                return [
                    'summary'   => 'Your Terms & Conditions forms the master binding contract between ReComm, its Customers, and independent Vendors.',
                    'checklist' => [
                        'Clarify that ReComm operates as a technology-enabled marketplace/facilitator connecting Customers with independent Vendors.',
                        'Specify that Vendors bear legal responsibility for product genuineness, MRP compliance, weights, packaging, and expiry dates.',
                        'Explain how delivery, payments, refunds, and available delivery windows (Immediate, Later, Lunch, Dinner, Custom) operate.',
                        'Include statutory Grievance Officer details (name, designation, email, phone, registered address) required by the Consumer Protection (E-Commerce) Rules, 2020.',
                        'Cover account security, prohibited products, customer conduct, intellectual property, disclaimers, limitation of liability, indemnification, force majeure, and governing law.',
                        'Cross-reference the standalone Privacy Policy and Refund & Cancellation Policy rather than duplicating their content.'
                    ],
                    'statutory' => 'Mandatory under the Information Technology Act, 2000 and Consumer Protection (E-Commerce) Rules, 2020.'
                ];

            case 'privacy_policy':
                return [
                    'summary'   => 'Your Privacy Policy must accurately describe how ReComm collects, uses, stores, shares, and retains personal data.',
                    'checklist' => [
                        'Explicitly disclose the capture and use of real-time GPS location for nearby-store discovery and delivery-radius checking.',
                        'Explain that delivery addresses and phone numbers are shared with Vendors and Delivery Partners solely for order fulfilment.',
                        'State that online payment credentials are handled exclusively by authorised, RBI-regulated Payment Service Providers.',
                        'Confirm that personal data is stored within India and protected in accordance with the Digital Personal Data Protection Act, 2023.',
                        'Detail user rights to access, correct, and request erasure of personal data, and how to escalate a grievance.',
                        'Include a named Grievance Officer / Data Protection contact with email, phone, and registered address.'
                    ],
                    'statutory' => 'Compliant with the Digital Personal Data Protection Act, 2023 (DPDP Act) and IT Rules, 2011.'
                ];

            case 'refund_policy':
                return [
                    'summary'   => 'Your Refund & Cancellation Policy sets clear expectations for order cancellations, returns, and turnaround times.',
                    'checklist' => [
                        'Define the cancellation cutoff (e.g. before the vendor begins packing or dispatches the parcel).',
                        'Set realistic reporting windows for damaged or defective items (e.g. 24 hours for perishables, 48 hours for packaged goods).',
                        'Specify non-returnable categories (opened food, fresh produce, hygiene products) to protect local merchants.',
                        'State exact refund timelines: 3-7 days for online payment methods; 2-4 days via UPI/NEFT for COD orders.',
                        'Explain that a 100% refund including delivery charges is issued if an order is cancelled by the vendor or platform.',
                        'Explain how partial refunds are calculated and how disputed or rejected claims can be escalated.'
                    ],
                    'statutory' => 'Required to prevent consumer disputes under Consumer Protection Act, 2019.'
                ];

            default:
                return [
                    'summary'   => 'Draft clear, concise policy terms adhering to relevant commercial standards.',
                    'checklist' => ['Maintain accurate headings', 'Specify contact methods', 'Review periodically'],
                    'statutory' => 'Standard commercial policy requirements.'
                ];
        }
    }
}

if (!function_exists('resolve_legal_page')) {
    /**
     * Resolves the current live legal page content.
     * If custom published_content exists in the database, returns it with status 'custom'.
     * Otherwise, seamlessly falls back to the shipped default content with status 'default'.
     *
     * @param string $slug
     * @return array
     */
    function resolve_legal_page($slug)
    {
        $slug = strtolower(trim($slug));
        $default = get_default_legal_page($slug);

        $result = [
            'slug'             => $slug,
            'title'            => $default['title'],
            'content'          => $default['content'],
            'status'           => 'default', // 'default' or 'custom'
            'is_default'       => true,
            'published_at'     => null,
            'updated_at'       => null,
            'has_draft'        => false,
            'draft_content'    => null,
            'raw_published'    => null,
        ];

        // Query database if database connection is available
        $row = null;
        if (function_exists('get_instance')) {
            $CI = &get_instance();
            if (isset($CI->db) && $CI->db->table_exists('legal_pages')) {
                $row = $CI->db->where('slug', $slug)->get('legal_pages')->row_array();
            }
        } elseif (isset($GLOBALS['mysqli']) && $GLOBALS['mysqli'] instanceof mysqli) {
            $slugEsc = $GLOBALS['mysqli']->real_escape_string($slug);
            $q = $GLOBALS['mysqli']->query("SELECT * FROM legal_pages WHERE slug = '{$slugEsc}' LIMIT 1");
            if ($q && $q->num_rows > 0) {
                $row = $q->fetch_assoc();
            }
        }

        if ($row) {
            $result['title'] = !empty($row['title']) ? $row['title'] : $default['title'];
            $result['has_draft'] = !empty($row['draft_content']);
            $result['draft_content'] = $row['draft_content'];
            $result['raw_published'] = $row['published_content'];
            $result['updated_at'] = $row['updated_at'];
            $result['published_at'] = $row['published_at'];

            if (!empty($row['published_content']) && $row['status'] === 'custom') {
                $result['content'] = $row['published_content'];
                $result['status'] = 'custom';
                $result['is_default'] = false;
            }
        }

        return $result;
    }
}
