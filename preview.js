function previewFile(fileName) {
    const carpetaNombre = new URLSearchParams(window.location.search).get("nombre");
    const filePath = `./descarga/${carpetaNombre}/${fileName}`;
    const previewContent = document.getElementById('preview-content');

    // Limpiar el contenido previo
    previewContent.innerHTML = '';

    // Verificar la extensión del archivo para determinar cómo previsualizar
    const extension = fileName.split('.').pop().toLowerCase();
    if (extension === 'jpg' || extension === 'jpeg' || extension === 'png') {
        // Previsualizar imagen
        previewContent.innerHTML = `<img src="${filePath}" alt="${fileName}" style="max-width: 50%; height: auto;">`;
    } else if (extension === 'pdf') {
        // Previsualizar PDF
        previewContent.innerHTML = `<iframe src="${filePath}" style="width: 100%; height: 500px;" frameborder="0"></iframe>`;
    } else {
        previewContent.innerHTML = '<p>Formato no soportado para previsualización.</p>';
    }
}