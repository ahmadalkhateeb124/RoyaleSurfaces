<?php
/**
 * A looping silent video where one has been uploaded, the photo otherwise.
 *
 * Used inside .about-2col blocks. Keeping it in one place means the playback
 * attributes, the poster and the missing-file fallback stay identical wherever
 * a video appears — and adding one to another page is three variables.
 *
 * Callers set, immediately before including this file:
 *   $mediaVideo    filename inside assets/videos  (e.g. 'about.mp4')
 *   $mediaFallback filename inside assets/images  — the poster frame, and what
 *                  renders when the video has not been uploaded yet
 *   $mediaAlt      alt text for the photo
 *   $mediaClip     optional — play only the first N seconds, then restart.
 *                  Useful when only the opening of a longer clip is worth
 *                  showing. Note the browser still downloads the whole file:
 *                  trimming the actual video is better for page weight.
 */

$mediaVideo    = $mediaVideo    ?? '';
$mediaFallback = $mediaFallback ?? 'hero-stone.jpg';
$mediaAlt      = $mediaAlt      ?? '';
$mediaClip     = $mediaClip     ?? 0;

$mediaPath = rtrim((string) $base_path, '/') . '/assets/videos/' . $mediaVideo;
?>
<?php if ($mediaVideo !== '' && is_file($mediaPath)): ?>
    <video class="about-video" autoplay loop muted playsinline preload="metadata"
        <?= $mediaClip > 0 ? 'data-clip="' . (float) $mediaClip . '"' : '' ?>
        poster="<?= e(asset('assets/images/' . $mediaFallback)) ?>"
        disablepictureinpicture disableremoteplayback tabindex="-1" aria-hidden="true"
        width="800" height="1000">
        <source src="<?= e(asset('assets/videos/' . $mediaVideo)) ?>" type="video/mp4" />
    </video>
<?php else: ?>
    <img src="<?= e(asset('assets/images/' . $mediaFallback)) ?>" alt="<?= e($mediaAlt) ?>"
        loading="lazy" width="800" height="1000" />
<?php endif; ?>

<?php unset($mediaVideo, $mediaFallback, $mediaAlt, $mediaClip, $mediaPath); ?>
