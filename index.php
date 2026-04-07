<?php
require_once 'db.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_invoice']) && $db_connected) {
    $invoice_no = $conn->real_escape_string($_POST['invoice_number']);
    $mobile = $conn->real_escape_string($_POST['mobile']);
    $name = $conn->real_escape_string($_POST['name']);
    $address = $conn->real_escape_string($_POST['address']);
    $gst_num = $conn->real_escape_string($_POST['gst_number']);
    $gst_slab = $conn->real_escape_string($_POST['gst_slab']);
    
    $stmt_cust = $conn->prepare("INSERT INTO Customer (mobile_number, name, address, gst_number) 
                                 VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE 
                                 name=VALUES(name), address=VALUES(address), gst_number=VALUES(gst_number)");
    $stmt_cust->bind_param("ssss", $mobile, $name, $address, $gst_num);
    $stmt_cust->execute();

    $products = $_POST['product'];
    $rates = $_POST['rate'];
    $qtys = $_POST['quantity'];
    $totals = $_POST['total'];
    
    $stmt_inv = $conn->prepare("INSERT INTO Invoice (invoice_number, product, rate, quantity, total, gst_slab, total_price) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");

    for ($i = 0; $i < count($products); $i++) {
        $prod = $products[$i];
        $rate = floatval($rates[$i]);
        $qty = intval($qtys[$i]);
        $row_total = floatval($totals[$i]);
        
        $row_total_price = $row_total + ($row_total * floatval($gst_slab) / 100);

        $stmt_inv->bind_param("ssdiddd", $invoice_no, $prod, $rate, $qty, $row_total, $gst_slab, $row_total_price);
        $stmt_inv->execute();
    }
    
    $message = "Invoice #$invoice_no saved successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DHT Solutions - Invoice Task</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .no-spinners::-webkit-inner-spin-button, .no-spinners::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        .no-spinners { -moz-appearance: textfield; }
    </style>
</head>
<body class="p-6">

<div class="max-w-5xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
    
    <?php if(!$db_connected): ?>
        <div class="bg-red-500 text-white p-4 text-center font-semibold">
            <?= $db_error ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" class="p-8">
        
        <?php if($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 text-center font-semibold">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <div class="space-y-4">
                <div class="flex flex-col">
                    <label class="font-semibold text-gray-700 mb-1">Invoice Number <span class="text-red-500">*</span></label>
                    <input type="text" name="invoice_number" required placeholder="INV-1001" class="border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="flex flex-col">
                    <label class="font-semibold text-gray-700 mb-1">Customer Mobile Number <span class="text-red-500">*</span></label>
                    <input type="text" name="mobile" id="mobile" required placeholder="Try '7499934522'" class="border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-500 outline-none">
                    <small class="text-gray-400 mt-1">Enter number and click outside to auto-fetch details</small>
                </div>
            </div>
            
            <div class="space-y-4 bg-gray-50 p-4 rounded-lg border">
                <div class="flex flex-col">
                    <label class="font-semibold text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" id="name" required class="border border-gray-300 rounded p-2 bg-white outline-none">
                </div>
                <div class="flex flex-col">
                    <label class="font-semibold text-gray-700 mb-1">Address</label>
                    <input type="text" name="address" id="address" required class="border border-gray-300 rounded p-2 bg-white outline-none">
                </div>
                <div class="flex flex-col">
                    <label class="font-semibold text-gray-700 mb-1">GST Number</label>
                    <input type="text" name="gst_number" id="gst_number" required class="border border-gray-300 rounded p-2 bg-white outline-none">
                </div>
            </div>
        </div>

        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Line Items</h3>
        <div class="overflow-x-auto mb-4">
            <table class="w-full text-left border-collapse" id="invoice-table">
                <thead>
                    <tr class="bg-gray-100 text-gray-700">
                        <th class="p-3 border font-semibold w-2/5">Product</th>
                        <th class="p-3 border font-semibold w-1/5">Rate (₹)</th>
                        <th class="p-3 border font-semibold w-1/5">Quantity</th>
                        <th class="p-3 border font-semibold w-1/5">Total (₹)</th>
                        <th class="p-3 border font-semibold text-center w-12">Action</th>
                    </tr>
                </thead>
                <tbody id="product-tbody">
                    <tr>
                        <td class="p-2 border"><input type="text" name="product[]" required class="w-full border-0 p-1 outline-none focus:ring-2 focus:ring-blue-500 rounded"></td>
                        <td class="p-2 border"><input type="number" step="0.01" name="rate[]" required class="rate no-spinners w-full border-0 p-1 outline-none focus:ring-2 focus:ring-blue-500 rounded text-right" placeholder="0.00"></td>
                        <td class="p-2 border"><input type="number" name="quantity[]" required class="qty no-spinners w-full border-0 p-1 outline-none focus:ring-2 focus:ring-blue-500 rounded text-right" placeholder="0"></td>
                        <td class="p-2 border"><input type="number" name="total[]" readonly class="row-total w-full border-0 p-1 bg-transparent text-right font-semibold outline-none" value="0.00"></td>
                        <td class="p-2 border text-center"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <button type="button" id="btn-add-row" class="mb-8 text-sm bg-blue-100 text-blue-700 font-semibold py-2 px-4 rounded hover:bg-blue-200 transition">
            + Add Another Product
        </button>

        <div class="flex flex-col md:flex-row justify-between items-end gap-6 mb-8 border-t pt-6">
            <div class="w-full md:w-1/2">
                <label class="font-semibold text-gray-700 mb-1 block">Amount in words</label>
                <textarea id="amount_words" readonly class="w-full border border-gray-300 bg-gray-50 rounded p-3 h-24 resize-none text-gray-600 font-medium"></textarea>
            </div>

            <div class="w-full md:w-1/3 space-y-4">
                <div class="flex justify-between items-center text-gray-600">
                    <span class="font-semibold">Subtotal:</span>
                    <span class="font-semibold">₹ <span id="display-subtotal">0.00</span></span>
                </div>
                <div class="flex justify-between items-center">
                    <label class="font-semibold text-gray-700">GST Dropdown</label>
                    <select name="gst_slab" id="gst_slab" class="border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-500 outline-none bg-white w-32">
                        <option value="5">5%</option>
                        <option value="12">12%</option>
                        <option value="18">18%</option>
                        <option value="28">28%</option>
                    </select>
                </div>
                <div class="flex justify-between items-center border-t-2 border-gray-800 pt-2 text-xl font-bold text-gray-900">
                    <span>Total Price</span>
                    <span>₹ <input type="text" id="display-total" readonly class="w-28 bg-transparent text-right outline-none cursor-default" value="0.00"></span>
                </div>
            </div>
        </div>

        <div class="text-right">
            <button type="submit" name="save_invoice" class="bg-blue-700 text-white font-bold py-3 px-10 rounded-lg hover:bg-blue-800 shadow-lg transition">
                SAVE INVOICE
            </button>
        </div>

    </form>
</div>

<script src="script.js"></script>

</body>
</html>