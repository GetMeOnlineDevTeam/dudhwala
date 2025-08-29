function previewImage(input, previewId = 'imagePreview') {
    const file = input.files[0];
    const reader = new FileReader();

    reader.onload = function (e) {
        document.getElementById(previewId).src = e.target.result;
    };

    if (file) {
        reader.readAsDataURL(file);
    }
}

function removeImage(inputId = 'imageInput', previewId = 'imagePreview') {
    document.getElementById(inputId).value = '';
    document.getElementById(previewId).src = '/assets/media/svg/avatars/blank.svg';
}
