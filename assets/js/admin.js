// Common JavaScript functions

// Delete confirmation
function confirmDelete(url, message) {
    if (confirm(message || 'Are you sure you want to delete this item?')) {
        window.location.href = url;
    }
}

// Toggle status
function toggleStatus(id, table, currentStatus) {
    const newStatus = currentStatus == 1 ? 0 : 1;
    
    $.ajax({
        url: `${table}/toggle-status`,
        type: 'POST',
        data: { id: id, status: newStatus },
        dataType: 'json',
        success: function(response) {
            if (response.status) {
                location.reload();
            } else {
                alert(response.message);
            }
        },
        error: function() {
            alert('Something went wrong');
        }
    });
}

// Image preview
function previewImage(input, targetId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $(`#${targetId}`).attr('src', e.target.result).show();
        }
        reader.readAsDataURL(input.files[0]);
    }
}