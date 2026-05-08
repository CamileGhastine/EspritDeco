document.getElementById('add-image-btn').addEventListener('click', () => {
    const container = document.getElementById('extra-images');
    const div = document.createElement('div');
    div.className = 'mb-2';
    div.innerHTML = `
        <input type="file" name="product_form[images][]" accept="image/jpeg,image/png,image/webp" class="form-control">
    `;
    container.appendChild(div);
});

window.deleteImage = async function deleteImage(btn) {
    const id = btn.dataset.id;
    const url = btn.dataset.url;
    const token = btn.dataset.token;

    const res = await fetch(url , {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `token=${token}`
    });

    const data = await res.json();
    if (data.success) {
        document.getElementById(`image-card-${id}`).remove();
        const modalEl = document.getElementById(`deleteModal-${id}`);
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();
    } else {
        alert(data.message ?? 'Erreur lors de la suppression.');
    }
}

window.setPrincipal = async function setPrincipal(btn) {
    const id = btn.dataset.id;
    const url = btn.dataset.url;
    const token = btn.dataset.token;
    
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `token=${token}`
    });

    const data = await res.json();
        if (data.success) {
            // Modification badge, bouton et bordures sur toutes les cartes
            document.querySelectorAll('[id^="badge-star-"]').forEach(el => el.classList.add('d-none'));
            document.querySelectorAll('[id^="btn-star-"]').forEach(el => el.classList.remove('d-none'));
            document.querySelectorAll('[id^="btn-delete-"]').forEach(el => el.classList.remove('d-none'));
            document.getElementById(`badge-star-${id}`).classList.remove('d-none');
            document.getElementById(`btn-star-${id}`).classList.add('d-none');
            document.getElementById(`btn-delete-${id}`).classList.add('d-none');
            document.querySelectorAll('.card-minotaur').forEach(el => el.classList.remove('border-primary','border-3'));
            btn.closest('.card-minotaur').classList.add('border-primary', 'border-3');
            // Mettre à jour la carte cliquée

        } else {
            alert(data.message ?? 'Erreur.');
        }
    }