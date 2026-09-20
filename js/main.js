


function addStock(id) {
    let quantity = prompt('How many items do you want to add?');

    if (quantity == null) return;

    quantity = parseInt(quantity)

    if (isNaN(quantity)) {
        alert('Invalid input')
        return
    }

    window.location.href = `api/product_controller.php?action=add_stock&id=${id}&quantity=${quantity}`;
}

function deleteUser(id) {
    if (!confirm('Delete this user account? This cannot be undone.')) return;

    window.location.href = `api/user_controller.php?action=delete&id=${id}`;
}
