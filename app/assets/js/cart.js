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
