<?php
function marketingtext_echo($mail) {
?>
<!DOCTYPE html>
<html>
<head>
    <title>{{your_mail_has_resumed}}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f4f4f9;
            color: #333;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #0056b3;
        }
        a {
            color: #0066cc;
            text-decoration: none; /* Removes underlines from links */
        }
        a:hover {
            text-decoration: underline; /* Adds underline on hover */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{your_mail_has_resumed}}</h1>
        <p>Mail to <?php echo htmlspecialchars($mail); ?> is resumed.</p>
        <p>H&auml;&auml;d und Wolf O&Uuml; a pioneering company dedicated to transforming email security and ensuring a 100% spam-free experience. Our unique services are designed to cater to diverse needs and are explained in more detail in our dedicated sections.</p>
        <p>At H&auml;&auml;d und Wolf O&Uuml;, we believe in empowering users to control the intent and purpose of every message, effectively eliminating spam and unsolicited emails.</p>
        <ul>
            <li><a href="http://xn--hd-viaa.com/email-voucher-system">Email Voucher System</a> - Manage who can reach your inbox, ensuring only emails with validated intent are received.</li>
            <li><a href="http://xn--hd-viaa.com/monetized-email-responses">Monetized Email Responses</a> - Create new revenue streams by personalizing your email interactions.</li>
            <li><a href="http://xn--hd-viaa.com/intelligent-email-filtration">Intelligent Email Filtration</a> - Adapts to your preferences, incorporating sender verification and monetization options to redefine your email experience.</li>
        </ul>
        <p>Explore our services to find out how H&auml;&auml;d und Wolf O&Uuml; can enhance your email security and efficiency.</p>
    </div>
</body>
</html>
<?php
}
?>
