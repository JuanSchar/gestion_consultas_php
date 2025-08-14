$(document).ready(function() {
  function toggleCampos() {
    var rol = $("select[name='rol']").val();
    if (rol == '2') {
      $("#campo-profesor").show();
      $("#campo-alumno").hide();
    } else if (rol == '3') {
      $("#campo-profesor").hide();
      $("#campo-alumno").show();
    } else {
      $("#campo-profesor").hide();
      $("#campo-alumno").hide();
    }
  }
  $("select[name='rol']").change(toggleCampos);
  toggleCampos();
});
