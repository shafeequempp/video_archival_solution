function selPicture(s){ if(s=="U") {$('#upload').click();} else {$('#back').click();}}
function readURL(input)
{
    var $modal = $('#modal');
    var image = document.getElementById('sample_image');
    var cropper;

    var done = function(url){
        image.src = url;
        $('#modal').modal('show');
    };
    if(input.files && input.files[0])
    {
        var reader = new FileReader();
        reader.onload = function(event)
        {
            done(reader.result);
        };
        reader.readAsDataURL(input.files[0]);
    }

    let isCropped = false;

    $modal.on('shown.bs.modal', function () {
        cropper = new Cropper(image, {
            aspectRatio: 32 / 11,
            viewMode: 2
        });
        isCropped = false; // Reset on open
    }).on('hidden.bs.modal', function () {
        cropper.destroy();
        cropper = null;
    
        if (!isCropped) {
            $('#upload').val(''); // Clear file input only if crop not clicked
            $('#eventBannerBase64').val(''); // Clear base64 value
        }
    });
    
    $('#crop').click(function () {
        const canvas = cropper.getCroppedCanvas({
            width: 1100,
            height: 382
        });
    
        canvas.toBlob(function (blob) {
            const reader = new FileReader();
            reader.readAsDataURL(blob);
            reader.onloadend = function () {
                $('#upload').attr('value', reader.result);
                $('#eventBannerBase64').val(reader.result);
                isCropped = true; // Mark that cropping was completed
                $('#modal').modal('hide');
            };
        });
    });
}
function downloadThis() {
    html2canvas(document.querySelector("#banner"), {onrendered: function(canvas) {var myImage = canvas.toDataURL(); console.log(myImage); downloadURI(myImage, "file.png");}})
}
function downloadURI(uri, name) {
    var link = document.createElement("a");
    link.download = name;
    link.href = uri;
    document.body.appendChild(link);
    link.click();
}
$(".backColor").colorPick({'palette': ["#207080", "#0c3466", "#621900", "#000000", "#f89220", "#124c66", "#314004", "#ac2a00", "#16002f", "#3f2000"], 'onColorSelected': function() {$("#banner").css({'backgroundColor': this.color});  $("#myModal").modal('hide'); }});
$(".headColor").colorPick({'palette': ["#207080", "#0c3466", "#621900", "#000000", "#f89220", "#124c66", "#314004", "#ac2a00", "#16002f", "#3f2000"], 'onColorSelected': function() {$("#heading").css({'color': this.color});  $("#myModal").modal('hide'); }});

$(".subColor").colorPick({'palette': ["#207080", "#0c3466", "#621900", "#000000", "#f89220", "#124c66", "#314004", "#ac2a00", "#16002f", "#3f2000"], 'onColorSelected': function() {$("#subheading").css({'color': this.color});   $("#myModal").modal('hide'); }});

var today = new Date();
var dd = String(today.getDate()).padStart(2, '0');
var mm = String(today.getMonth() + 1).padStart(2, '0');
var yyyy = today.getFullYear();

$("#dateBox").val(dd + '-' + mm + '-' + yyyy);
$("#timeBox").val(formatAMPM());

 function formatAMPM() {
  var date=new Date;
  var hours = date.getHours();
  var minutes = date.getMinutes();
  var ampm = hours >= 12 ? 'pm' : 'am';
  hours = hours % 12;
  hours = hours ? hours : 12; // the hour '0' should be '12'
  minutes = minutes < 10 ? '0'+minutes : minutes;
  var strTime = hours + ':' + minutes + ' ' + ampm;
  return strTime;
}


pasteAsTxt("heading");
pasteAsTxt("subheading");
function pasteAsTxt(did) {
    const editorEle = document.getElementById(did);
    editorEle.addEventListener('paste', function(e) {
    e.preventDefault();
    const text = (e.clipboardData)
        ? (e.originalEvent || e).clipboardData.getData('text/plain')
        : (window.clipboardData ? window.clipboardData.getData('Text') : '');
    if (document.queryCommandSupported('insertText')) {
        document.execCommand('insertText', false, text);
    } else {
        const range = document.getSelection().getRangeAt(0);
        range.deleteContents();

        const textNode = document.createTextNode(text);
        range.insertNode(textNode);
        range.selectNodeContents(textNode);
        range.collapse(false);

        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
    }
  });
}

function showPopop() {
    $('#myModal').modal({keyboard: true, show: true,  backdrop: false});

}
function changeBG(input)
{
  if (input.files && input.files[0])
  {
    var reader = new FileReader();
    reader.onload = function (e)
    {
        $('#upload').attr('value',e.target.result);
        document.getElementById('eventBannerBase64').value = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}