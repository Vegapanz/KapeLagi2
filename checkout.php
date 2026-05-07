<?php
include 'config/session.php';
include 'config/db.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: signin.php');
    exit;
}

// Handle reset phone request
if (isset($_GET['reset_phone'])) {
    unset($_SESSION['phone_verified']);
    unset($_SESSION['verified_phone']);
    header('Location: checkout.php');
    exit;
}

$user_id = get_user_id();

// Get user data
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

$saved_full_address = trim((string) ($_SESSION['user_address'] ?? ($user['address'] ?? '')));
$saved_block_lot = '';
$saved_street_address = $saved_full_address;
if ($saved_full_address !== '' && strpos($saved_full_address, ',') !== false) {
    [$parsed_block_lot, $parsed_street_address] = array_map('trim', explode(',', $saved_full_address, 2));
    $saved_block_lot = $parsed_block_lot;
    $saved_street_address = $parsed_street_address;
}

// Check if phone is verified for this checkout session
$phone_verified = isset($_SESSION['phone_verified']) && $_SESSION['phone_verified'] === true;
$verified_phone = $_SESSION['verified_phone'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - KapeLagi</title>
    <link rel="icon" type="image/png" href="assets/Images/favicon.png">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Smooch+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/checkout.css">
    <link rel="stylesheet" href="assets/css/phone-verification.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <!-- Leaflet Routing Machine CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
</head>

<body>
    <!-- Navigation Bar -->
    <?php include 'components/navbar.php'; ?>

    <!-- Checkout Section -->
    <section class="checkout-section">
        <div class="container-lg">
            <div class="checkout-header">
                <a href="menu.php" class="back-link"><i class="fas fa-arrow-left"></i> Back</a>
            </div>

            <div class="checkout-content" id="checkoutContent">
                <!-- Left: Delivery Information -->
                <div class="checkout-form">
                    <h2 class="checkout-title">Delivery Information</h2>

                    <form id="checkoutForm">
                        <div class="form-row">
                            <div class="form-col">
                                <label>Name</label>
                                <input type="text" name="customer_name" class="form-input" value="<?php echo $user['name'] ?? ''; ?>" required>

                        <!-- <div class="form-group">
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                <input type="checkbox" id="useMapPin" style="width:auto;">
                                Use pin on map instead of typing the full address
                            </label>
                            <small class="form-text text-muted">Leave this unchecked if you want to enter the address manually.</small>
                        </div> -->
                            </div>
                            <div class="form-col">
                                <label>Mobile Number</label>
                                <div class="phone-field-wrapper">
                                    <input
                                        type="tel"
                                        id="phoneInput"
                                        name="customer_phone"
                                        class="form-input"
                                        placeholder="eg. 09xxxxxxxxx"
                                        value="<?php echo $verified_phone ?? $user['phone'] ?? ''; ?>"
                                        pattern="09[0-9]{9}"
                                        <?php echo $phone_verified ? 'readonly' : ''; ?>
                                        required>
                                    <?php if ($phone_verified): ?>
                                        <a href="checkout.php?reset_phone=1" class="btn-change-phone">Change</a>
                                    <?php else: ?>
                                        <button type="button" id="sendOtpBtn" class="btn-send-otp-inline">Verify</button>
                                    <?php endif; ?>
                                </div>  
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <label>Email</label>
                                <input type="email" name="customer_email" class="form-input" value="<?php echo $user['email'] ?? ''; ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Address 1</label>
                            <div style="display:flex;gap:10px;align-items:stretch;position:relative;">
                                <button type="button" id="openMapModalBtn" aria-label="Open map picker" style="width:52px;min-width:52px;border:1px solid #b18b63;background:#b18b63;color:#fff;border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                                    <i class="fas fa-map-marker-alt"></i>
                                </button>
                                <div style="flex:1;position:relative;">
                                    <input id="delivery_address" type="text" name="delivery_address" class="form-input" placeholder="Street, house no., etc." value="<?php echo htmlspecialchars($saved_street_address); ?>" autocomplete="off" required>
                                    <div id="addressSuggestions" class="address-suggestions"></div>
                                </div>
                            </div>
                            <input type="hidden" id="lat" name="lat">
                            <input type="hidden" id="lng" name="lng">
                            <small class="form-text text-muted">Click the map pin icon to pick a location instead of typing the address.</small>
                        </div>

                        <div class="form-group">
                            <label>Block and Lot</label>
                            <input type="text" name="address_2" class="form-input" placeholder="Street Number (blk,lot,phase, etc.)" value="<?php echo htmlspecialchars($saved_block_lot); ?>">
                        </div>

                        <h3 class="section-title mt-5">Payment Method</h3>
                        <div class="payment-placeholder">
                            <div class="payment-methods">
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="COD" checked required>
                                    <span>Cash on Delivery</span>
                                </label>

                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="GCASH" required>
                                    <span>GCash</span>
                                </label>
                            </div>
                        </div>

                    </form>
                </div>

                <!-- Right: Order Summary -->
                <div class="order-summary-column">
                    <h2 class="checkout-title">Order Summary</h2>

                    <div class="order-summary">
                        <div id="cartItems" class="cart-items">
                            <!-- Items will be loaded by JavaScript -->
                        </div>

                        <div class="summary-divider"></div>

                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span id="subtotal">0.00₱</span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span id="shipping">0.00₱</span>
                        </div>
                        <input type="hidden" id="shipping_fee" name="shipping_fee" value="0">
                        <input type="hidden" id="distance_km" name="distance_km" value="0">

                        <div class="summary-divider"></div>

                        <div class="summary-row total">
                            <span>Total</span>
                            <span id="total">0.00₱</span>
                        </div>

                        <button class="checkout-btn" id="checkoutBtn">Check Out</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Picker Modal -->
    <div class="modal fade" id="mapPickerModal" tabindex="-1" aria-labelledby="mapPickerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius:18px;overflow:hidden;">
                <div class="modal-header" style="border-bottom:1px solid rgba(0,0,0,.08);">
                    <h5 class="modal-title" id="mapPickerModalLabel">Pick a delivery location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="background:#f7f3ea;">
                    <div id="map" style="height:420px;border:1px solid #ddd;border-radius:14px;margin-bottom:12px;"></div>
                    <small id="mapAddress" class="form-text text-muted">Click the map to pick a location — address will appear here.</small>
                    <small id="routeInfo" class="form-text text-muted d-block mt-2">Route distance will appear here after you choose a pin.</small>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$phone_verified): ?>
        <div id="phoneVerificationModal" class="phone-verification-modal" aria-hidden="true">
            <div class="verification-card" role="dialog" aria-modal="true" aria-labelledby="phoneVerificationTitle">
                <button type="button" class="verification-close" id="closeVerificationModal" aria-label="Close verification modal">&times;</button>
                <h2 id="phoneVerificationTitle">Verify Your Phone Number</h2>
                <p>Enter the code we sent before placing your order.</p>

                <div class="verification-form">
                    <div class="form-group">
                        <label>Enter Verification Code</label>
                        <input
                            type="text"
                            id="otpInput"
                            name="otp"
                            class="form-input"
                            placeholder="000000"
                            maxlength="6"
                            pattern="[0-9]{6}"
                            required>
                        <small id="otpTimer">Code expires in: <span id="timerDisplay">10:00</span></small>
                    </div>

                    <div class="otp-actions">
                        <button type="button" id="verifyOtpBtn" class="btn-verify-otp">Verify Code</button>
                        <button type="button" id="resendOtpBtn" class="btn-resend-otp" style="display: none;">Resend Code</button>
                    </div>

                    <div id="verificationMessage" class="verification-message"></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <?php include 'components/footer.php'; ?>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.checkoutPhoneVerified = <?php echo $phone_verified ? 'true' : 'false'; ?>;
    </script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Leaflet Routing Machine JS -->
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mapEl = document.getElementById('map');
            if (!mapEl) return;

            const mapModalEl = document.getElementById('mapPickerModal');
            const openMapModalBtn = document.getElementById('openMapModalBtn');
            const mapModal = mapModalEl ? new bootstrap.Modal(mapModalEl) : null;

            // Wider bounding box so the full Dasmariñas area stays visible
            const dasmBounds = L.latLngBounds([14.24, 120.86], [14.40, 121.04]);

            const map = L.map('map', {
                maxBounds: dasmBounds,
                maxBoundsViscosity: 1.0,
                minZoom: 12,
                maxZoom: 19,
                zoomControl: true
            }).fitBounds(dasmBounds);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            map.on('moveend', () => {
                if (!dasmBounds.contains(map.getCenter())) map.panInsideBounds(dasmBounds);
            });

            function refreshMapSize() {
                setTimeout(() => map.invalidateSize(), 50);
            }

            if (openMapModalBtn && mapModal) {
                openMapModalBtn.addEventListener('click', function () {
                    mapModal.show();
                });
            }

            if (mapModalEl) {
                mapModalEl.addEventListener('shown.bs.modal', function () {
                    refreshMapSize();
                });
            }

            let marker = null;
            let storeMarker = null;
            let routingControl = null;
            const routeInfoEl = document.getElementById('routeInfo');

            // Custom icons (colored markers)
            const userIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });
            const storeIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
                shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            // Address of the store to geocode and place on the map
            const storeAddress = '5th Street, Santa Cristina 1, Bagong Bayan, Dasmariñas, Cavite, Calabarzon, 4115, Philippines';

            // Geocode the store address using Nominatim and add a marker
            (async function addStoreMarker() {
                try {
                    const searchUrl = `https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(storeAddress)}&limit=1`;
                    const res = await fetch(searchUrl);
                    const results = await res.json();
                    if (results && results.length) {
                        const s = results[0];
                        const storeLatLng = L.latLng(parseFloat(s.lat), parseFloat(s.lon));
                        storeMarker = L.marker(storeLatLng, { icon: storeIcon }).addTo(map);
                        storeMarker.bindPopup(`<b>Store</b><br>${s.display_name}`);

                        storeMarker.on('click', async function () {
                            if (!marker) {
                                if (routeInfoEl) routeInfoEl.textContent = 'Pick a location on the map first.';
                                storeMarker.openPopup();
                                return;
                            }

                            const from = marker.getLatLng();
                            const to = storeLatLng;
                            drawRoute(from, to);
                        });
                        // If the user already picked a pin, draw the route immediately
                        if (marker) {
                            try { drawRoute(marker.getLatLng(), storeLatLng); } catch (e) { console.warn(e); }
                        }
                    } else {
                        if (routeInfoEl) routeInfoEl.textContent = 'Unable to locate store address.';
                    }
                } catch (err) {
                    if (routeInfoEl) routeInfoEl.textContent = 'Error locating store address.';
                }
            })();
            const mapAddressEl = document.getElementById('mapAddress');
            const deliveryInput = document.getElementById('delivery_address');

            async function reverseGeocode(latlng) {
                if (!mapAddressEl) return;
                mapAddressEl.textContent = 'Finding address...';
                try {
                    const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(latlng.lat)}&lon=${encodeURIComponent(latlng.lng)}`;
                    const res = await fetch(url, { method: 'GET' });
                    if (!res.ok) throw new Error('Network response was not ok');
                    const data = await res.json();
                    const display = data.display_name || '';
                    if (display) {
                        if (deliveryInput) {
                            deliveryInput.value = display;
                            // Clear Block and Lot field since map provides the full address
                            const address2Input = document.querySelector('input[name="address_2"]');
                            if (address2Input) address2Input.value = '';
                        }
                        mapAddressEl.textContent = display;
                    } else {
                        mapAddressEl.textContent = 'Address not found';
                    }
                } catch (err) {
                    mapAddressEl.textContent = 'Unable to fetch address';
                }
            }

            function updateInputs(latlng) {
                document.getElementById('lat').value = latlng.lat;
                document.getElementById('lng').value = latlng.lng;
                if (deliveryInput && mapAddressEl) reverseGeocode(latlng);
            }

            window.KapeCheckout = window.KapeCheckout || {};
            window.KapeCheckout.setMapLocation = function (lat, lng, displayName) {
                const latlng = L.latLng(lat, lng);
                const address2Input = document.querySelector('input[name="address_2"]');
                if (address2Input) {
                    address2Input.value = '';
                }
                if (!marker) {
                    marker = L.marker(latlng, { draggable: true, icon: userIcon }).addTo(map);
                    marker.on('dragend', ev => {
                        const pos = ev.target.getLatLng();
                        updateInputs(pos);
                        if (storeMarker) drawRoute(pos, storeMarker.getLatLng());
                    });
                } else {
                    marker.setLatLng(latlng);
                }
                map.panTo(latlng);
                updateInputs(latlng);
                if (storeMarker) {
                    drawRoute(latlng, storeMarker.getLatLng());
                }
                if (mapAddressEl && displayName) {
                    mapAddressEl.textContent = displayName;
                }
            };

            // Draw route between two points and show distance
            function drawRoute(from, to) {
                if (!from || !to) return;
                if (routingControl) {
                    try { map.removeControl(routingControl); } catch (e) { console.warn(e); }
                    routingControl = null;
                }

                routingControl = L.Routing.control({
                    waypoints: [from, to],
                    router: L.Routing.osrmv1({ serviceUrl: 'https://router.project-osrm.org/route/v1', profile: 'driving' }),
                    fitSelectedRoutes: false,
                    show: false,
                    addWaypoints: false,
                    lineOptions: { styles: [{ color: 'blue', weight: 5 }] },
                    createMarker: function() { return null; }
                }).addTo(map);

                routingControl.on('routesfound', function (e) {
                    const routes = e.routes;
                    if (routes && routes.length) {
                        const summary = routes[0].summary || {};
                        const meters = summary.totalDistance ?? summary.total_distance ?? summary.distance ?? 0;
                        const km = (meters / 1000).toFixed(2);
                        if (window.KapeCheckout && typeof window.KapeCheckout.setShippingByDistance === 'function') {
                            window.KapeCheckout.setShippingByDistance(km, meters);
                        } else if (routeInfoEl) {
                            routeInfoEl.textContent = `Distance: ${km} km (${Math.round(meters)} m)`;
                        }
                    } else {
                        if (routeInfoEl) routeInfoEl.textContent = 'No route found.';
                    }
                });

                routingControl.on('routingerror', function (e) {
                    console.error('Routing error', e);
                    if (routeInfoEl) routeInfoEl.textContent = 'Routing error';
                    const shippingFeeInput = document.getElementById('shipping_fee');
                    const distanceKmInput = document.getElementById('distance_km');
                    if (shippingFeeInput) shippingFeeInput.value = '0';
                    if (distanceKmInput) distanceKmInput.value = '0';
                });
            }

            map.on('click', e => {
                if (!dasmBounds.contains(e.latlng)) return;
                if (!marker) {
                    marker = L.marker(e.latlng, { draggable: true, icon: userIcon }).addTo(map);
                    marker.on('dragend', ev => {
                        const pos = ev.target.getLatLng();
                        updateInputs(pos);
                        // if store marker exists, redraw route
                        if (storeMarker) drawRoute(pos, storeMarker.getLatLng());
                    });
                    // if store marker exists already, draw route immediately
                    if (storeMarker) drawRoute(e.latlng, storeMarker.getLatLng());
                } else {
                    marker.setLatLng(e.latlng);
                    // if store marker exists, redraw route
                    if (storeMarker) drawRoute(e.latlng, storeMarker.getLatLng());
                }
                updateInputs(e.latlng);
            });
        });
    </script>
    <!-- Phone Verification JavaScript -->
    <script src="assets/js/phone-verification.js"></script>
    <!-- Checkout JavaScript -->
    <script src="assets/js/checkout.js"></script>
</body>

</html>