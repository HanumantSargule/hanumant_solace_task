# Studio Frame Composer

A PHP + HTML5 Canvas application for composing framed artwork previews with layered controls.

## Core Features

- Shows a real-time preview on the left using HTML5 Canvas.
- Shows control panels on the right for instant updates.
- Supports three configurable layers:
  - Canvas Size
  - Artwork Source
  - Border Pattern
- Supports selecting sample nature images and uploading a custom image.
- Resizes uploaded images on the server side using PHP GD.
- Draws frame borders with repeated frame pattern images on Canvas.

## Requirement Coverage

### 1) Draw a Preview (Left Side) using HTML5 Canvas
Implemented.
- Canvas preview is rendered on the left panel.
- Rendering is handled using the Frame engine in js/frame.js.

### 2) Right side controls to modify preview (layer based)
Implemented.
- Tabbed layer UI is present on the right side:
  - Canvas Size
  - Artwork Source
  - Border Pattern
- User selections trigger preview rerender.

### 3) Enter Size -> Preview updates to entered dimensions
Implemented.
- Width and height inputs are provided.
- Update Preview button applies entered size.
- Live input updates also trigger rerender when values are valid.

### 4) Artwork layer (gallery images + upload)
Implemented.
- Sample nature images are loaded from samples/.
- Uploaded images are listed from upload/.
- File input allows user image selection and upload.
- Selected photo is rendered into the preview area.

### 5) Border layer with selectable pattern options
Implemented.
- Multiple frame options (fr1.png, fr2.png, fr3.png) are available.
- Clicking a frame option updates the third visual layer in preview.

### 6) Frame as small piece/stick/pattern repeated over canvas
Implemented in rendering logic.
- Frame drawing uses repeated image tiling along frame edges.
- Tiling is done in draw loops inside js/frame.js.

## Implementation Highlights

1. Layered control architecture
- Added/used tab-based layer controls for Size, Photo, and Frame operations.

2. Real-time canvas rerender workflow
- Added/used rerender logic that refreshes preview when:
  - frame changes
  - photo changes
  - size changes

3. Size driven preview dimensions
- Canvas width and height are bound to entered width and height values.
- Minimum safe image dimensions are enforced in rendering path.

4. Artwork source handling
- Added/used sample image source selection from samples/.
- Added/used uploaded image selection from upload/.
- Added/used browser preview path for selected local image.

5. Server-side upload and resize pipeline
- Upload handler validates uploaded file existence and type.
- Uses PHP GD to open JPG/PNG/GIF.
- Resizes to submitted width/height and stores output in upload/.
- Stores selected image path in session for reuse after redirect.

6. Border pattern rendering
- Frame image is tiled repeatedly across left/right/top/bottom edges.
- Produces patterned border effect around the mounted photo region.

## Current Notes

- No compile/lint errors are currently reported in index.php, action.php, or js/frame.js.
- Final visual quality depends on border asset design (fr1.png, fr2.png, fr3.png).

## File Map

- index.php: Main page UI, tabs, interactions, and canvas rendering setup.
- action.php: Upload and image resize processing.
- css/app.css: Modern UI styles in a dedicated stylesheet.
- js/app.js: Control-panel behavior and canvas rerender orchestration.
- js/frame.js: Canvas frame/mount/photo drawing engine.
- samples/: Built-in sample photos.
- upload/: Uploaded and processed images.
- .gitignore: Ignores generated upload thumbnails from source control.
- fr1.png, fr2.png, fr3.png: Border assets used for pattern rendering.

## Cleanup And Formatting

- Removed generated upload files from the repository working set.
- Added upload ignore rules so temporary outputs are not committed.
- Moved page CSS/JS into separate files for a standard maintainable structure.
- Applied consistent naming and formatting for PHP and JavaScript modules.

## Run Instructions (WAMP)

1. Place project under `C:/wamp64/www/`.
2. Ensure the folder name in `www` matches the URL path you open in browser.
  - Example for this repository: `C:/wamp64/www/hanumant_demo`
3. Start Apache + PHP in WAMP.
4. Open in browser:
  - `http://localhost/hanumant_demo/index.php`

If you rename the folder, update only the URL segment after `localhost/`.

## Suggested Next Improvements

1. Add export/download button for final preview image.
2. Add basic responsive tuning for smaller screens.
3. Add automated browser tests (for layer switching and preview updates).
