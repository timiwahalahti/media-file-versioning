jQuery(document).ready(function ($) {
    $('#media_version_upload_btn').on('click', function () {
        const fileInput = $('#media_version_upload')[0].files[0];

        if (!fileInput) {
            $('#media_version_success')
                .text('Please select a file to upload.')
                .css('color', 'red');
            return;
        }

        const formData = new FormData();
        formData.append('file', fileInput);
        formData.append('action', 'media_versioning_upload');
        formData.append('attachment_id', $('#post_ID').val());
        formData.append('nonce', MediaVersioning.nonce);

        $.ajax({
            url: MediaVersioning.ajax_url,
            type: 'POST',
            processData: false,
            contentType: false,
            data: formData,
            success: function (response) {
                if (response.success) {
                    // Clear the file input
                    $('#media_version_upload').val('');

                    // Show success message
                    $('#media_version_success')
                        .text('File uploaded and replaced successfully.')
                        .css('color', 'green');

                    // Dynamically update the version list
                    const list = $('#media_versions_list');
                    list.empty(); // Clear the existing list

                    // Add the current version
                    const currentFile = response.data.current_file;
                    const currentItem = `
                        <li>
                            <strong>Current Version:</strong> 
                            <a href="${currentFile.url}" target="_blank">
                                ${currentFile.name}
                            </a> (${new Date(currentFile.time * 1000).toLocaleString()})
                        </li>`;
                    list.append(currentItem);

                    // Add all previous versions
                    if (response.data.versions.length > 0) {
                        response.data.versions.forEach(function (version) {
                            const listItem = `
                                <li>
                                    <strong>Previous Version:</strong> 
                                    <a href="${version.url}" target="_blank">
                                        ${version.url.split('/').pop()}
                                    </a> (${new Date(version.time * 1000).toLocaleString()})
                                </li>`;
                            list.append(listItem);
                        });
                    }
                } else {
                    $('#media_version_success')
                        .text(response.data.message)
                        .css('color', 'red');
                }
            },
            error: function () {
                $('#media_version_success')
                    .text('An error occurred while uploading the file.')
                    .css('color', 'red');
            },
        });
    });
});
