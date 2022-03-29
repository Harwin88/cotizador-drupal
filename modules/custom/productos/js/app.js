$(document).ready(function () {
    $("#edit-submit-event-request-search").click(function () {
        var myTable = $("#edit-table");
         var f = new Date();
         var dia=f.getDate();
         var mes=f.getMonth() +1;
         var anio=f.getFullYear();
         var hora = f.getHours();
         var minuto = f.getMinutes();
         var segundo = f.getSeconds();
         var fecha_unida = dia+""+mes+""+anio+""+hora+""+minuto+""+segundo;    excel = new ExcelGen({
        "file_name": "data-"+fecha_unida+".xlsx",
        "src": myTable,
        "show_header": true,
        "exclude_selector": ".xl_none"
       });
       excel.generate();
    });

    alert($("#").vl());

});
