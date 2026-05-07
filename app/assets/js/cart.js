const clearBtn = document.getElementById('clear-cart');

if (clearBtn) {
    clearBtn.addEventListener('click', () => {
            fetch(clearBtn.dataset.url, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector('.offcanvas-body').innerHTML = '<p>Votre panier est vide</p>';
                document.querySelector('#nbr-items').textContent = '0';
                const offcanvasEl = document.getElementById('cart-offcanvas');
                const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);

                if (offcanvas) {
                    offcanvas.hide();
                }
            }
        })
        .catch(error => console.error(error));
    });
}

document.addEventListener('click', (e) => {
    const btn = e.target.closest('.increase-cart');
    if (!btn) return;

    fetch(btn.dataset.url, { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            const row = btn.closest('tr');
            row.querySelector('.item-qty').textContent = data.newQty;
            row.querySelector('.line-total').textContent = data.linePrice.toFixed(2) + ' €';
            document.getElementById('cart-total').textContent = data.totalPrice.toFixed(2) + ' €';
            document.getElementById('nbr-items').textContent = data.totalQty;
        })
        .catch(err => console.error(err));
});