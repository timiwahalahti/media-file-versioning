# Media File Versioning

A WordPress® plugin to track and manage versions of media files in the WordPress® Media Library. 

This plugin allows you to upload new versions of media files, maintain a history of previous versions, and seamlessly display these versions in the WordPress® admin interface and on the front end.


## Features

- **Version Control**: Automatically saves previous versions of media files when a new version is uploaded.
- **Easy Uploads**: Upload new versions directly from the Media Library.
- **Admin Interface**: View the current version and a list of previous versions in a user-friendly interface.
- **Shortcode Support**: Display the current and previous versions on the front end using a simple shortcode.
- **Customizable Display**: Automatically orders versions from most recent to oldest for better readability.

## Installation

1. **Download the Plugin**  
Clone or download this repository as a ZIP file.

```
git clone https://github.com/robertdevore/media-file-versioning.git
```

2. **Install the Plugin**

    - Log in to your WordPress® admin dashboard.
    - Go to `Plugins > Add New > Upload Plugin`.
    - Upload the `media-file-versioning.zip` file.
    - Activate the plugin.
3. **You're Ready!**

## Usage

### 1. **Managing Media Versions**

- Go to the **Media Library**.
- Edit an existing media item.
- Use the **Media Versioning** meta box to:
    - Upload a new version.
    - View the current version and all previous versions.

### 2. **Shortcode**

Use the `[mfv]` shortcode to display versions on the front end.

#### Shortcode Attributes:

- `id`: (Required) The ID of the media item.

#### Example:
```
[mfv id="123"]
```

This will display the current and previous versions of the media item with ID 123 in a structured format.

## Meta Box Interface

The plugin adds a **Media Versioning** meta box to the media edit screen:
- **Current Version**: Displays the current file, including its name and upload time.
- **Previous Versions**: Lists previous versions in descending order (most recent first) under a single heading.

## JavaScript Integration

### Admin UI

The plugin includes JavaScript to:

- Dynamically update the version list after a new file is uploaded.
- Reverse the order of previous versions for consistent display.

## Contributing

We welcome contributions to enhance the functionality of this plugin. To contribute:

1. Fork the repository.
2. Create a feature branch:

```
git checkout -b feature/your-feature-name
```

3. Commit your changes:

```
git commit -m "Add your feature description here"
````

4. Push to your branch:

````
git push origin feature/your-feature-name
````

5. Create a Pull Request on GitHub.

## Support

If you encounter issues or have feature requests, please [open an issue](https://github.com/robertdevore/media-file-versioning/issues).

## License

This plugin is licensed under the [GPL-2.0+ License](http://www.gnu.org/licenses/gpl-2.0.txt).
* * *