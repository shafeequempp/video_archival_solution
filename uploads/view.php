<?php

// include '../config.php';
include '../layouts/header.php';
include '../connect.php';
?>

<!-- Tagify CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">

<div class="col-lg-12 grid-margin stretch-card">
  <div class="card">
    <div class="card-body">
        <div class="card-title d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Video Library</h4>
            <div>
                <a href="create.php" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> Upload New</a>
            </div>
        </div>

      <!-- Search and Tag Filter Inputs -->
      <div class="row mb-3">
        <div class="col-md-6"></div>
        <div class="col-md-3">
          <input type="text" id="liveSearch" class="form-control" placeholder="Search event, cameraman, location" style="height: 38px !important;">
        </div>
        <div class="col-md-3">
          <input type="text" id="tagSearch" class="form-control" placeholder="Filter by faces (tags)">
        </div>
      </div>

      <!-- Table -->
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Event</th>
              <th>Faces</th>
              <th>Location</th>
              <th>Cameraman</th>
              <th>Date</th>
              <th>Filename</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="resultsTable">
            <tr><td colspan="7" class="text-center text-muted">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="previewModalLabel">Video Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <video id="previewVideo" width="100%" controls autoplay muted>
          <source id="previewSource" src="" type="video/mp4">
          Your browser does not support the video tag.
        </video>
        <div class="mt-3 text-start">
          <p><strong>Duration:</strong> <span id="videoDuration">Loading...</span></p>
          <p><strong>Resolution:</strong> <span id="videoResolution">Loading...</span></p><?php if ($_SESSION['role'] == 'ADMIN') { ?>
          <a id="downloadLink" class="btn btn-sm btn-success" href="#" download>Download Full Video</a><?php } ?>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Scripts -->
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  const searchInput = document.getElementById('liveSearch');
  const tagInput = document.getElementById('tagSearch');
  const resultsTable = document.getElementById('resultsTable');

  const tagify = new Tagify(tagInput);

  function debounce(fn, delay) {
    let timer;
    return function () {
      clearTimeout(timer);
      timer = setTimeout(fn, delay);
    };
  }

  async function fetchResults() {
    const search = searchInput.value.trim();
    const tags = tagify.value.map(t => t.value).join(',');
    const params = new URLSearchParams({ search, tag: tags });

    const res = await fetch('search_uploads.php?' + params.toString());
    const html = await res.text();
    resultsTable.innerHTML = html;
  }

  const debouncedFetch = debounce(fetchResults, 300);
  searchInput.addEventListener('keyup', debouncedFetch);
  tagInput.addEventListener('keyup', debouncedFetch);
  tagInput.addEventListener('change', debouncedFetch);

  resultsTable.addEventListener('click', function (e) {
    if (e.target.classList.contains('badge')) {
      const tagValue = e.target.innerText.trim();
      if (!tagify.value.find(t => t.value === tagValue)) {
        tagify.addTags([tagValue]);
      }
    }
  });

  fetchResults();
</script>

<script>
function softDelete(id) {
  Swal.fire({
    title: 'Are you sure?',
    text: "Do you really want to delete this video?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, delete it!',
    reverseButtons: true
  }).then((result) => {
    if (result.isConfirmed) {
      fetch('delete_uploads.php?id=' + id, { method: 'POST' })
        .then(() => {
          Swal.fire({
            title: 'Deleted!',
            text: 'Video has been deleted.',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
          });
          fetchResults();
        });
    }
  });
}
</script>

<script>
fetch('get_all_faces.php')
  .then(res => res.json())
  .then(whitelist => {
    tagify.settings.whitelist = whitelist;
  });
</script>

<script>
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.preview-btn');
  if (btn) {
    const previewUrl = btn.dataset.preview;
    const ogVideoUrl = btn.dataset.ogvideo;
    const duration = btn.dataset.duration;
    const resolution = btn.dataset.resolution;

    const videoElement = document.getElementById('previewVideo');
    const sourceElement = document.getElementById('previewSource');
    const durationEl = document.getElementById('videoDuration');
    const resolutionEl = document.getElementById('videoResolution');
    const downloadLink = document.getElementById('downloadLink'); // may be null

    // Update video source
    sourceElement.src = previewUrl;
    videoElement.load();
    videoElement.muted = true;
    videoElement.play();

    // Update duration and resolution
    durationEl.textContent = duration || 'N/A';
    resolutionEl.textContent = resolution || 'N/A';

    // Update download link only if it exists
    if (downloadLink) {
      downloadLink.href = ogVideoUrl;
    }

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
  }
});

// Reset video when modal is closed
document.getElementById('previewModal').addEventListener('hidden.bs.modal', function () {
  const videoElement = document.getElementById('previewVideo');
  videoElement.pause();
  videoElement.currentTime = 0;
});
</script>
<?php include '../layouts/footer.php'; ?>
