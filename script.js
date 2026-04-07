document.addEventListener('DOMContentLoaded', () => {
    
    const mobileInput = document.getElementById('mobile');
    mobileInput.addEventListener('blur', async () => {
        const val = mobileInput.value.trim();
        if(val.length > 0) {
            try {
                // Pointing to the new API file
                const response = await fetch(`get_customer.php?mobile=${val}`);
                const res = await response.json();
                if(res.found) {
                    document.getElementById('name').value = res.data.name;
                    document.getElementById('address').value = res.data.address;
                    document.getElementById('gst_number').value = res.data.gst_number;
                }
            } catch (err) { console.error("Error fetching customer", err); }
        }
    });

    const tbody = document.getElementById('product-tbody');
    
    document.getElementById('btn-add-row').addEventListener('click', () => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="p-2 border"><input type="text" name="product[]" required class="w-full border-0 p-1 outline-none focus:ring-2 focus:ring-blue-500 rounded"></td>
            <td class="p-2 border"><input type="number" step="0.01" name="rate[]" required class="rate no-spinners w-full border-0 p-1 outline-none focus:ring-2 focus:ring-blue-500 rounded text-right" placeholder="0.00"></td>
            <td class="p-2 border"><input type="number" name="quantity[]" required class="qty no-spinners w-full border-0 p-1 outline-none focus:ring-2 focus:ring-blue-500 rounded text-right" placeholder="0"></td>
            <td class="p-2 border"><input type="number" name="total[]" readonly class="row-total w-full border-0 p-1 bg-transparent text-right font-semibold outline-none" value="0.00"></td>
            <td class="p-2 border text-center"><button type="button" class="btn-remove text-red-500 font-bold hover:bg-red-100 px-2 rounded">X</button></td>
        `;
        tbody.appendChild(tr);
    });

    tbody.addEventListener('click', (e) => {
        if(e.target.classList.contains('btn-remove')) {
            e.target.closest('tr').remove();
            calculateGrandTotal();
        }
    });

    tbody.addEventListener('input', (e) => {
        if(e.target.classList.contains('rate') || e.target.classList.contains('qty')) {
            const tr = e.target.closest('tr');
            const rate = parseFloat(tr.querySelector('.rate').value) || 0;
            const qty = parseFloat(tr.querySelector('.qty').value) || 0;
            const rowTotal = (rate * qty).toFixed(2);
            tr.querySelector('.row-total').value = rowTotal;
            calculateGrandTotal();
        }
    });

    document.getElementById('gst_slab').addEventListener('change', calculateGrandTotal);

    function calculateGrandTotal() {
        let subtotal = 0;
        document.querySelectorAll('.row-total').forEach(input => {
            subtotal += parseFloat(input.value) || 0;
        });
        
        document.getElementById('display-subtotal').innerText = subtotal.toFixed(2);

        const gstPerc = parseFloat(document.getElementById('gst_slab').value) || 0;
        const grandTotal = subtotal + (subtotal * gstPerc / 100);
        
        document.getElementById('display-total').value = grandTotal.toFixed(2);
        
        document.getElementById('amount_words').value = numberToWordsIndian(Math.round(grandTotal));
    }

    function numberToWordsIndian(num) {
        if (num === 0) return 'Zero Rupees Only';
        const a = ['','One ','Two ','Three ','Four ', 'Five ','Six ','Seven ','Eight ','Nine ','Ten ','Eleven ','Twelve ','Thirteen ','Fourteen ','Fifteen ','Sixteen ','Seventeen ','Eighteen ','Nineteen '];
        const b = ['', '', 'Twenty','Thirty','Forty','Fifty', 'Sixty','Seventy','Eighty','Ninety'];
        
        if ((num = num.toString()).length > 9) return 'Amount too large';
        const n = ('000000000' + num).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
        if (!n) return ''; 
        let str = '';
        str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + ' ' + a[n[1][1]]) + 'Crore ' : '';
        str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + ' ' + a[n[2][1]]) + 'Lakh ' : '';
        str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + ' ' + a[n[3][1]]) + 'Thousand ' : '';
        str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + ' ' + a[n[4][1]]) + 'Hundred ' : '';
        str += (n[5] != 0) ? ((str != '') ? 'and ' : '') + (a[Number(n[5])] || b[n[5][0]] + ' ' + a[n[5][1]]) + 'Rupees Only' : 'Rupees Only';
        return str.trim();
    }
});