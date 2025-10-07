<?php
include '../layouts/header.php';
include '../connect.php';

// Validate & get ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid ID");
}
$id = intval($_GET['id']);

// Fetch record
$stmt = $conn->prepare("SELECT * FROM video_metadata WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Record not found");
}
$data = $result->fetch_assoc();
$stmt->close();
?>
<style>
  #results {
      list-style: none;
      margin: 0;
      padding: 0;
      background: #fff;
      border: 1px solid #ccc;
      max-height: 200px;
      overflow-y: auto;
      position: absolute;
      width: 85%; /* Full width */
      z-index: 1000;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      border-radius: 4px;
  }

  #results li {
      padding: 10px;
      cursor: pointer;
      transition: background 0.2s;
  }

  #results li:hover,
  #results li.active {
      background: #f0f0f0;
  }

  #location {
      width: 100%;
      padding: 10px;
      box-sizing: border-box;
  }

  #loading {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.85); /* light overlay */
      display: none;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      z-index: 9999;
      backdrop-filter: blur(4px);
  }

  /* Loader GIF */
  #loading img {
      width: 180px;
      height: 180px;
      /* margin-bottom: 15px; */
  }

  /* Loader text */
  #loading p {
      font-size: 1.2rem;
      font-weight: 500;
      color: #333;
      text-align: center;
      letter-spacing: 0.5px;
  }


</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places&callback=initAutocomplete" async defer></script>

<form class="forms-sample" method="POST" id="uploadForm" action="update_uploads.php" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
    <div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="card shadow-lg rounded-4 p-4" style="width: 100%; max-width: 700px;">
            <div class="card-body">
                <h4 class="card-title mb-3">Edit Video Metadata</h4>

                <!-- Current Video Preview -->
                <?php if (!empty($data['filename'])): ?>
                    <div class="mb-3">
                        <video width="100%" controls>
                            <source src="../<?= htmlspecialchars($data['filename']) ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                <?php endif; ?>

                <!-- Replace Video -->
                <div class="form-group mb-3">
                    <label>Replace Video (optional)</label>
                    <input type="file" class="form-control" name="video_upload" accept="video/*" />
                </div>

                <!-- Event -->
                <div class="form-group mb-3">
                    <label>Event / Incident <small class="text-danger">*</small></label>
                    <input type="text" class="form-control" name="event" value="<?= htmlspecialchars($data['event']) ?>" required>
                </div>

                <!-- Faces -->
                <div class="form-group mb-3">
                    <label>Faces</label>
                    <input id="faces" name="faces" value="<?= htmlspecialchars($data['faces']) ?>" class="form-control">
                </div>

                <!-- Location -->
                <div class="form-group mb-3">
                    <label>Location</label>
                    <input type="text" class="form-control" name="location" id="location" value="<?= htmlspecialchars($data['location']) ?>" autocomplete="off">
                    <ul id="results"></ul>
                </div>

                <!-- Cameraman -->
                <div class="form-group mb-3">
                    <label>Cameraman</label>
                    <input type="text" class="form-control" name="cameraman" value="<?= htmlspecialchars($data['cameraman']) ?>">
                </div>

                <!-- Description -->
                <div class="form-group mb-3">
                    <label>Description</label>
                    <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($data['description']) ?></textarea>
                </div>

                <!-- Date -->
                <div class="form-group mb-3">
                    <label>Date</label>
                    <input type="date" class="form-control" name="date" value="<?= htmlspecialchars($data['date']) ?>">
                </div>

                <!-- Usage Rights -->
                <h5>Usage Rights</h5>
                <div class="form-group mb-3">
                    <select class="form-control" name="usage_rights" required>
                        <option value="">-- Select Usage Rights --</option>
                        <option value="ready" <?= $data['usage_rights'] === 'ready' ? 'selected' : '' ?>>Ready to use</option>
                        <option value="embargo" <?= $data['usage_rights'] === 'embargo' ? 'selected' : '' ?>>Embargo till date</option>
                        <option value="private" <?= $data['usage_rights'] === 'private' ? 'selected' : '' ?>>Private</option>
                    </select>
                </div>

                <!-- Embargo Date -->
                <div class="form-group mb-3">
                    <label>Embargo Date</label>
                    <input type="date" class="form-control" name="embargo_date" value="<?= htmlspecialchars($data['embargo_date']) ?>">
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">Update Metadata</button>
                </div>
            </div>
        </div>
    </div>
</form>

<div id="loading" style="display:none;">
    <img src="../src/assets/images/pleasewait.gif" alt="Loading..." /><br>
    <p>Video Uploading Please Wait...</p>
</div>
<script>
  document.getElementById("uploadForm").addEventListener("submit", function() {
      document.getElementById("loading").style.display = "flex";
  });
</script>
<script>
    const tagInput = document.querySelector('#faces');
    const tagify = new Tagify(tagInput);

    fetch('get_all_faces.php')
    .then(res => res.json())
    .then(whitelist => {
        tagify.settings.whitelist = whitelist;
    });
</script>
<script>

const input = document.getElementById("location");
const resultsList = document.getElementById("results");
let currentIndex = -1; // Track which suggestion is highlighted

input.addEventListener("input", function() {
    const query = input.value.trim();
    currentIndex = -1; // Reset selection on new input

    if (query.length < 2) {
        resultsList.innerHTML = "";
        return;
    }

    fetch(`https://api.geoapify.com/v1/geocode/autocomplete?text=${encodeURIComponent(query)}&apiKey=30a68defe4f443129371ce8bbd1736e4`)
        .then(response => response.json())
        .then(data => {
            resultsList.innerHTML = "";
            data.features.forEach(feature => {
                const li = document.createElement("li");
                li.textContent = feature.properties.formatted;

                li.addEventListener("click", () => {
                    selectAddress(feature.properties.formatted);
                });

                resultsList.appendChild(li);
            });
        })
        .catch(err => console.error(err));
});

// Keyboard navigation
input.addEventListener("keydown", function(e) {
    const items = resultsList.querySelectorAll("li");

    if (e.key === "ArrowDown") {
        e.preventDefault();
        currentIndex = (currentIndex + 1) % items.length;
        updateActive(items);
    } 
    else if (e.key === "ArrowUp") {
        e.preventDefault();
        currentIndex = (currentIndex - 1 + items.length) % items.length;
        updateActive(items);
    } 
    else if (e.key === "Enter") {
        e.preventDefault();
        if (currentIndex >= 0 && items[currentIndex]) {
            selectAddress(items[currentIndex].textContent);
        }
    }
});

function updateActive(items) {
    items.forEach(item => item.classList.remove("active"));
    if (items[currentIndex]) {
        items[currentIndex].classList.add("active");
        items[currentIndex].scrollIntoView({ block: "nearest" });
    }
}

function selectAddress(address) {
    input.value = address;
    resultsList.innerHTML = "";
}


</script>
<?php include '../layouts/footer.php'; ?>
