<?php


// Encode HTML special characters
function encode($value)
{
    return htmlentities($value);
}

// Generate <input type='hidden'>
function html_hidden($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='hidden' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='text'>
function html_text($key, $attr = '', $value = null)
{
    $finalValue = $value ?? $GLOBALS[$key] ?? '';
    $safeValue = encode($finalValue);
    echo "<input type='text' id='$key' name='$key' value='$safeValue' $attr>";
}

// Generate <input type='password'>
function html_password($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='password' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='number'>
function html_number($key, $min = '', $max = '', $step = '', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='number' id='$key' name='$key' value='$value'
                 min='$min' max='$max' step='$step' $attr>";
}

// Generate <input type='search'>
function html_search($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='search' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='date'>
function html_date($key, $min = '', $max = '', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='date' id='$key' name='$key' value='$value'
                 min='$min' max='$max' $attr>";
}

// Generate <input type='time'>
function html_time($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='time' id='$key' name='$key' value='$value' $attr>";
}

// Generate <textarea>
function html_textarea($key, $attr = '',$value = null)
{
    $finalValue = $value ?? $GLOBALS[$key] ?? '';
    $safeValue = encode($finalValue);
    echo "<textarea id='$key' name='$key' $attr>$safeValue</textarea>";
}

// Generate SINGLE <input type='checkbox'>
function html_checkbox($key, $label = '', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    $status = $value == 1 ? 'checked' : '';
    echo "<label><input type='checkbox' id='$key' name='$key' value='1' $status $attr>$label</label>";
}

// Generate <input type='checkbox'> list
function html_checkboxes($key, $items, $br = false)
{
    $values = $GLOBALS[$key] ?? [];
    if (!is_array($values)) $values = [];

    echo '<div>';
    foreach ($items as $id => $text) {
        $state = in_array($id, $values) ? 'checked' : '';
        echo "<label><input type='checkbox' id='{$key}_$id' name='{$key}[]' value='$id' $state>$text</label>";
        if ($br) {
            echo '<br>';
        }
    }
    echo '</div>';
}

// Generate <input type='radio'> list
function html_radios($key, $items, $br = false)
{
    $value = encode($GLOBALS[$key] ?? '');
    echo '<div>';
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'checked' : '';
        echo "<label><input type='radio' id='{$key}_$id' name='$key' value='$id' $state>$text</label>";
        if ($br) {
            echo '<br>';
        }
    }
    echo '</div>';
}

// Generate <select>
function html_select($key, $items, $default = '- Select One -', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' $attr>";
    if ($default !== null) {
        echo "<option value=''>$default</option>";
    }
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'selected' : '';
        echo "<option value='$id' $state>$text</option>";
    }
    echo '</select>';
}

// Generate <input type='file'>
function html_file($key, $accept = '', $attr = '')
{
    echo "<input type='file' id='$key' name='$key' accept='$accept' $attr>";
}

// Generate table headers <th>
function table_headers($fields, $sort, $dir, $href = '')
{
    foreach ($fields as $k => $v) {
        $d = 'asc'; // Default direction
        $c = '';    // Default class

        if ($k == $sort) {
            $d = $dir == 'asc' ? 'desc' : 'asc';
            $c = $dir;
        }

        echo "<th><a href='?sort=$k&dir=$d&$href' class='$c'>$v</a></th>";
    }
}
function showToast()
{
    // Check which session key is set
    $type = null;
    $msg = '';

    if (isset($_SESSION['flash_error']) && !empty($_SESSION['flash_error'])) {
        $type = 'error';
        $msg = $_SESSION['flash_error'];
        unset($_SESSION['flash_error']);
    } elseif (isset($_SESSION['flash_warning']) && !empty($_SESSION['flash_warning'])) {
        $type = 'warning';
        $msg = $_SESSION['flash_warning'];
        unset($_SESSION['flash_warning']);
    } elseif (isset($_SESSION['flash_success']) && !empty($_SESSION['flash_success'])) {
        $type = 'success';
        $msg = $_SESSION['flash_success'];
        unset($_SESSION['flash_success']);
    }

    // Return early if no message
    if (!$type) return;

    // Configuration for icons (using generic UTF-8 symbols)
    $config = [
        'error'   => ['icon' => '&#10006;', 'title' => 'Error'],
        'warning' => ['icon' => '&#9888;',  'title' => 'Warning'],
        'success' => ['icon' => '&#10004;', 'title' => 'Success'],
    ];

    // Handle array messages
    $toastText = is_array($msg) ? implode('<br>', $msg) : $msg;

    // Output HTML
?>
    <style>
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-family: system-ui, -apple-system, sans-serif;
            overflow: hidden;
            /* Keeps progress bar inside corners */
            z-index: 9999;
            min-width: 300px;
            max-width: 400px;

            /* Entrance Animation */
            animation: slideIn 0.5s ease-out forwards;
            transition: opacity 0.5s ease, transform 0.5s ease;
        }

        /* Flex container for the content */
        .toast-notification .toast-body {
            display: flex;
            align-items: flex-start;
            padding: 16px;
            color: #333;
            gap: 12px;
        }

        /* Color Themes */
        .toast-error .toast-icon {
            color: #e74c3c;
        }

        .toast-error .toast-progress {
            background-color: #e74c3c;
        }

        .toast-warning .toast-icon {
            color: #f39c12;
        }

        .toast-warning .toast-progress {
            background-color: #f39c12;
        }

        .toast-success .toast-icon {
            color: #2ecc71;
        }

        .toast-success .toast-progress {
            background-color: #2ecc71;
        }

        /* Icon Styling */
        .toast-icon {
            font-size: 1.2rem;
            line-height: 1;
        }

        /* Text Styling */
        .toast-text {
            flex: 1;
            display: flex;
            flex-direction: column;
            font-size: 14px;
        }

        .toast-title {
            font-weight: 700;
            margin-bottom: 4px;
        }

        .toast-message {
            color: #666;
            line-height: 1.4;
        }

        /* Close Button */
        .toast-close {
            background: none;
            border: none;
            color: #999;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0;
            margin-left: 10px;
            line-height: 1;
        }

        .toast-close:hover {
            color: #333;
        }

        /* Progress Bar - Absolutely positioned at bottom */
        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 4px;
            width: 100%;
            animation: progress 5s linear forwards;
        }

        /* Hiding State (Added via JS) */
        .toast-notification.hide {
            opacity: 0;
            transform: translateX(20px);
        }

        /* Animations */
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes progress {
            from {
                width: 100%;
            }

            to {
                width: 0;
            }
        }
    </style>
    <div id="toast-notification" class="toast-notification toast-<?php echo $type; ?>">
        <div class="toast-body">
            <span class="toast-icon"><?php echo $config[$type]['icon']; ?></span>
            <div class="toast-text">
                <span class="toast-title"><?php echo $config[$type]['title']; ?></span>
                <span class="toast-message"><?php echo $toastText; ?></span>
            </div>
            <button class="toast-close" onclick="closeToast()">&times;</button>
        </div>
        <div class="toast-progress"></div>
    </div>

    <script>
        // Auto-initialize the logic
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                closeToast();
            }, 5000); // 5 seconds (must match CSS animation time)
        });

        function closeToast() {
            const toast = document.getElementById('toast-notification');
            if (toast) {
                toast.classList.add('hide'); // Trigger fade out
                setTimeout(() => toast.remove(), 500); // Remove from DOM after fade out
            }
        }
    </script>
<?php
}
