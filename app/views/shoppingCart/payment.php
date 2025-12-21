<?php
require_once __DIR__ . '/../../helpers/html.php';
require_once __DIR__ . '/../../helpers/request.php';
require_once __DIR__ . '/../../controllers/paymentController.php';

$controller = new PaymentController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->process();
    exit;
}
$data = $controller->index(true);
if (!is_array($data)) {
    exit;
}
extract($data);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Gateway</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/public/css/payment.css">
    <script src="https://js.stripe.com/v3/"></script>
</head>

<body>

    <div class="container">
        <div class="payment-card">

            <div class="header-amount">
                <p class="amount-label">Payment ID: <?= encode($paymentId) ?></p>
                <div class="amount-display">RM <?= encode($formattedAmount) ?></div>
            </div>

            <form action="payment.php?payment_id=<?= encode($paymentId) ?>" method="POST" id="paymentForm" novalidate>
                <input type="hidden" name="payment_id" value="<?= encode($paymentId) ?>">

                <input type="hidden" name="stripePaymentId" id="stripePaymentId">

                <?php if ($paymentMethod === 'Credit Card'): ?>
                    <div id="credit-card-section">
                        <h3 style="margin-bottom: 20px; color: var(--primary-pink); text-align: center;">
                            <!-- <i class="far fa-credit-card"></i> Card Details -->
                            <i class="far fa-credit-card"></i> Secure Payment
                        </h3>

                        <div class="form-group">
                            <!-- <label class="form-label">Card Number</label>
                            <input type="text" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19" id="cc-input" required>
                            <small class="error-message" id="cc-input-error"></small>
                        </div>

                        <div class="form-group" style="display:flex; gap:15px;">
                            <div style="flex:1">
                                <label class="form-label">Expiry (MM/YYYY)</label>
                                <input type="text" class="form-control" placeholder="MM/YYYY" maxlength="7" id="cc-exp" required>
                                <small class="error-message" id="cc-exp-error"></small>
                            </div>
                            <div style="flex:1">
                                <label class="form-label">CVV</label>
                                <input type="password" class="form-control" placeholder="123" maxlength="3" id="cc-cvv" required>
                                <small class="error-message" id="cc-cvv-error"></small>
                            </div> -->
                            <label class="form-label">Card Information</label>
                            <div id="card-element" class="form-control" style="padding: 12px;"></div>
                            <div id="card-errors" class="error-message" style="display:none; color:#dc3545; margin-top:5px;"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Card Holder Name</label>
                            <!-- <input type="text" class="form-control" placeholder="Name on Card" id="cc-name" required>
                            <small class="error-message" id="cc-name-error"></small> -->
                            <input type="text" class="form-control" id="cardholder-name" placeholder="Name on Card" required>
                            <small class="error-message" id="cardholder-name-error" style="display:none; color:#dc3545;"></small>
                        </div>

                        <!-- <button type="submit" class="pay-btn">
                            Confirm Payment
                        </button> -->

                        <button type="button" id="stripe-submit-btn" class="pay-btn">
                            Pay RM <?= encode($formattedAmount) ?>
                        </button>
                    </div>
                <?php endif; ?>


                <?php if ($paymentMethod === 'TNG'): ?>
                    <div id="tng-section">
                        <div class="tng-wrapper">
                            <div class="tng-tabs">
                                <button type="button" class="tng-tab-btn active" onclick="switchTngTab('qr')">
                                    <i class="fas fa-qrcode"></i> QR Code
                                </button>
                                <button type="button" class="tng-tab-btn" onclick="switchTngTab('phone')">
                                    <i class="fas fa-mobile-alt"></i> Phone Login
                                </button>
                            </div>
                            <div id="tng-qr-content" class="tng-content">
                                <img src="/public/images/touch-n-go.png" class="tng-logo" alt="TNG">
                                <p style="font-size: 0.9rem;color:#666;">Scan to Pay</p>
                                <div class="qr-box">
                                    <img src="/public/images/qr_code.jpg" class="qr-img" alt="QR">
                                </div>
                                <p style="font-size: 0.8rem; color:#999;">Scan the QR code using you Touch 'n Go eWallet app to complete the payment.</p>
                            </div>

                            <div id="tng-phone-content" class="tng-content" style="display: none;">
                                <img src="/public/images/touch-n-go.png" class="tng-logo" alt="TNG">
                                <p style="font-size: 0.9rem; margin-bottom: 20px; color:#666;">Log in to TNG eWallet</p>
                                <div class="phone-input-group">
                                    <div class="prefix-box">+60</div>
                                    <input type="text" class="phone-field" placeholder="Enter phone number">
                                </div>
                                <small class="error-message" id="tng-phone-error" style="display:none; width:100%; text-align:left; margin-bottom:15px;"></small>
                                <div class="pin-label">6-digit PIN</div>
                                <div class="pin-container">
                                    <input type="password" class="pin-box" maxlength="1">
                                    <input type="password" class="pin-box" maxlength="1">
                                    <input type="password" class="pin-box" maxlength="1">
                                    <input type="password" class="pin-box" maxlength="1">
                                    <input type="password" class="pin-box" maxlength="1">
                                    <input type="password" class="pin-box" maxlength="1">
                                </div>
                                <small class="error-message" id="tng-pin-error" style="display:none; width:100%; text-align:center; margin-top:5px;"></small>
                            </div>
                        </div>

                        <button type="submit" class="pay-btn">
                            Complete Payment
                        </button>
                    </div>
                <?php endif; ?>

            </form>
        </div>
    </div>

    <div class="modal-overlay" id="leaveModalOverlay" style="display: none;">
        <div class="modal-content feedback-modal">
            <div class="modal-icon warning" style="color: #f39c12;">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
            <h3 class="modal-title centered">Processing...</h3>
            <p class="modal-message" style="margin-bottom: 20px;">
                Your request is being processed. If you leave this page now, your transaction might be cancelled.
            </p>
            <div class="modal-actions" style="justify-content: center; gap: 15px;">
                <button type="button" class="modal-btn btn-secondary" onclick="stayOnPage()">Stay</button>
                <button type="button" class="modal-btn btn-danger" onclick="leavePage()">Leave</button>
            </div>
        </div>
    </div>

    <script>
        const paymentMethod = '<?= encode($paymentMethod) ?>';
        let isSubmitting = false;
        const paymentForm = document.getElementById('paymentForm');
        // if (paymentForm) {
        //     paymentForm.addEventListener('submit', function() {
        //         isSubmitting = true;
        //     });
        // }

        function showLeaveModal() {
            document.getElementById('leaveModalOverlay').style.display = 'flex';
        }

        function stayOnPage() {
            document.getElementById('leaveModalOverlay').style.display = 'none';
            history.pushState(null, null, location.href);
        }

        function leavePage() {
            isSubmitting = true;
            const paymentConfig = window.paymentConfig || {};
            const custId = paymentConfig.customerId;

            if (custId) {
                window.location.href = `/app/views/order/customerOrderHistory.php?id=${custId}&status=pending_payment`;
            } else {
                console.error("Missing Customer ID");
                window.location.href = '/app/views/shoppingCart/cart.php?status=pending_payment';
            }
        }

        history.pushState(null, null, location.href);
        window.addEventListener('popstate', function(event) {
            if (!isSubmitting) {
                showLeaveModal();
            }
        });

        window.addEventListener('beforeunload', function(e) {
            if (!isSubmitting) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            }
        });

        // Stripe Payment Logic
        const stripeBtn = document.getElementById('stripe-submit-btn');

        if (paymentMethod === 'Credit Card' && stripeBtn) {
            const stripe = Stripe('pk_test_51Sg74oBZXdC5koAQQ3tfKsDvX0fJHGOHzxO5OV1bUii2GxEG7ajPDXMnc6XK1p2LKmlQK1gpRLxyiJgGXXQ4fl7300qw5VIN3m');
            const elements = stripe.elements();

            const style = {
                base: {
                    color: '#333',
                    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                    fontSmoothing: 'antialiased',
                    fontSize: '16px',
                    '::placeholder': {
                        color: '#888'
                    }
                },
                invalid: {
                    color: '#dc3545',
                    iconColor: '#dc3545'
                }
            };

            const card = elements.create('card', {
                style: style
            });
            card.mount('#card-element');

            card.on('change', function(event) {
                const displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                    displayError.style.display = 'block';
                } else {
                    displayError.textContent = '';
                    displayError.style.display = 'none';
                }
            });

            // Stripe Button Click Handler
            stripeBtn.addEventListener('click', async function(ev) {
                ev.preventDefault();

                clearErrors();
                const nameInput = document.getElementById('cardholder-name');
                if (!nameInput.value.trim()) {
                    showError('cardholder-name', 'Please enter Card Holder Name');
                    return;
                }

                if (/\d/.test(nameInput.value)) {
                    showError('cardholder-name', 'Card Holder Name cannot contain numbers');
                    return;
                }

                // Disable button to prevent multiple clicks
                stripeBtn.disabled = true;
                stripeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                isSubmitting = true;

                const clientSecret = '<?= $clientSecret ?? '' ?>';

                const result = await stripe.confirmCardPayment(clientSecret, {
                    payment_method: {
                        card: card,
                        billing_details: {
                            name: nameInput.value
                        }
                    }
                });

                if (result.error) {
                    //Processing error
                    const errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                    errorElement.style.display = 'block';

                    stripeBtn.disabled = false;
                    stripeBtn.textContent = 'Try Again';
                    isSubmitting = false;
                } else {
                    // Payment succeeded
                    if (result.paymentIntent.status === 'succeeded') {
                        document.getElementById('stripePaymentId').value = result.paymentIntent.id;
                        paymentForm.submit();
                    }
                }
            });
        }


        if (paymentForm) {
            paymentForm.addEventListener('submit', function(e) {
                if (document.getElementById('stripePaymentId').value) {
                    return;
                }

                if (paymentMethod === 'Credit Card') {
                    e.preventDefault();
                    return;
                }

                clearErrors();
                let isValid = true;

                if (paymentMethod === 'TNG') {
                    const phoneSection = document.getElementById('tng-phone-content');

                    if (phoneSection && phoneSection.style.display !== 'none') {
                        const phoneInput = document.querySelector('.phone-field');
                        let rawPhone = phoneInput.value.trim();

                        let cleanPhone = rawPhone.replace(/[^0-9]/g, '');

                        if (cleanPhone.startsWith('0')) {
                            cleanPhone = cleanPhone.substring(1);
                        }

                        if (!cleanPhone) {
                            phoneInput.style.borderColor = 'var(--error-red)';
                            showTngError('tng-phone-error', 'Please enter TNG phone number');
                            isValid = false;
                        } else if (cleanPhone.startsWith('11')) {
                            if (cleanPhone.length !== 10) {
                                phoneInput.style.borderColor = 'var(--error-red)';
                                showTngError('tng-phone-error', '011 numbers must be 10 digits (after +60)');
                                isValid = false;
                            }
                        } else if (/^1[02-9]/.test(cleanPhone)) {
                            if (cleanPhone.length !== 9) {
                                phoneInput.style.borderColor = 'var(--error-red)';
                                showTngError('tng-phone-error', '012-019 numbers must be 9 digits (after +60)');
                                isValid = false;
                            }
                        } else {
                            phoneInput.style.borderColor = 'var(--error-red)';
                            showTngError('tng-phone-error', 'Invalid format. Phone must start with 01x or 1x');
                            isValid = false;
                        }

                        let pinCode = '';
                        let pinComplete = true;
                        pinBoxes.forEach(box => {
                            if (box.value === '') pinComplete = false;
                            pinCode += box.value;
                        });

                        if (!pinComplete || pinCode.length !== 6) {
                            pinBoxes.forEach(b => b.style.borderColor = 'var(--error-red)');
                            showTngError('tng-pin-error', 'Please enter your 6-digit TNG PIN');
                            isValid = false;
                        }
                    }
                }

                /*
                if (paymentMethod === 'Credit Card') {
                    const cardNum = document.getElementById('cc-input').value.replace(/\s/g, '');
                    if (!cardNum || cardNum.length < 16) {
                        showError('cc-input', 'Invalid Card Number (16 digits)');
                        isValid = false;
                    }

                    const name = document.getElementById('cc-name').value;
                    if (!name) {
                        showError('cc-name', 'Required');
                        isValid = false;
                    } else if (/\d/.test(name)) {
                        showError('cc-name', 'Name cannot contain numbers');
                        isValid = false;
                    }

                    const cvv = document.getElementById('cc-cvv').value;
                    if (!cvv || cvv.length < 3) {
                        showError('cc-cvv', 'Required (3 digits)');
                        isValid = false;
                    }

                    const exp = document.getElementById('cc-exp').value;
                    const expParts = exp.split('/');
                    if (!exp || expParts.length !== 2) {
                        showError('cc-exp', 'Format: MM/YYYY');
                        isValid = false;
                    } else if (expParts.length === 2) {
                        const month = parseInt(expParts[0], 10);
                        const year = parseInt(expParts[1], 10);
                        const now = new Date();
                        const currentYear = now.getFullYear();
                        const currentMonth = now.getMonth() + 1;

                        if (month < 1 || month > 12) {
                            showError('cc-exp', 'Invalid Month (01-12)');
                            isValid = false;
                        } else if (year < currentYear || (year === currentYear && month < currentMonth)) {
                            showError('cc-exp', 'Card Expired');
                            isValid = false;
                        }
                    }
                }
                */

                if (!isValid) {
                    e.preventDefault();
                } else {
                    isSubmitting = true;
                }
            });
        }

        function showError(inputId, message) {
            const input = document.getElementById(inputId);
            const errorSmall = document.getElementById(inputId + '-error');

            if (input) input.classList.add('is-invalid');

            if (errorSmall) {
                errorSmall.textContent = message;
                errorSmall.style.display = 'block';
            }
        }

        function showTngError(errorId, message) {
            const errorSmall = document.getElementById(errorId);
            if (errorSmall) {
                errorSmall.textContent = message;
                errorSmall.style.display = 'block';
            }
        }

        function clearErrors() {
            document.querySelectorAll('.form-control, .phone-field, .pin-box').forEach(input => {
                input.style.borderColor = '';
                input.classList.remove('is-invalid');
            });
            document.querySelectorAll('.error-message').forEach(small => {
                small.style.display = 'none';
                small.textContent = '';
            });
        }

        /*
        const ccInput = document.getElementById('cc-input');
        if (ccInput) {
            ccInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/[^\d]/g, '').replace(/(.{4})/g, '$1 ').trim();
            });
        }

        const ccCvv = document.getElementById('cc-cvv');
        if (ccCvv) {
            ccCvv.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/[^\d]/g, '').replace(/(.{4})/g, '$1 ').trim();
            });
        }

        const ccExp = document.getElementById('cc-exp');
        if (ccExp) {
            ccExp.addEventListener('input', function(e) {
                let input = e.target.value.replace(/[^\d]/g, '');
                if (input.length > 2) input = input.substring(0, 2) + '/' + input.substring(2, 6);
                e.target.value = input;
            });
        }
        */

        // --- TNG Logic ---
        function switchTngTab(tabName) {
            const qrContent = document.getElementById('tng-qr-content');
            const phoneContent = document.getElementById('tng-phone-content');
            const tabs = document.querySelectorAll('.tng-tab-btn');

            tabs.forEach(btn => btn.classList.remove('active'));

            if (tabName === 'qr') {
                qrContent.style.display = 'flex';
                phoneContent.style.display = 'none';
                tabs[0].classList.add('active');
            } else {
                qrContent.style.display = 'none';
                phoneContent.style.display = 'flex';
                tabs[1].classList.add('active');
            }
        }

        // PIN Code Logic
        const pinBoxes = document.querySelectorAll('.pin-box');
        pinBoxes.forEach((box, index) => {
            box.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
                if (e.target.value.length === 1 && index < pinBoxes.length - 1) {
                    pinBoxes[index + 1].focus();
                }
            });
            box.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace') {
                    if (box.value === '' && index > 0) {
                        pinBoxes[index - 1].focus();
                    }
                }
            });
        });

        window.paymentConfig = {
            paymentMethod: '<?= encode($paymentMethod) ?>',
            stripeKey: 'pk_test_51Sg74oBZXdC5koAQQ3tfKsDvX0fJHGOHzxO5OV1bUii2GxEG7ajPDXMnc6XK1p2LKmlQK1gpRLxyiJgGXXQ4fl7300qw5VIN3m',
            clientSecret: '<?= $clientSecret ?? '' ?>',

            customerId: '<?= $_SESSION['customerId'] ?? '' ?>'
        };
    </script>

</body>

</html>