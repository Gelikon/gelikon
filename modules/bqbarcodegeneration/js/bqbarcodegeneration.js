
/**
*  Module Name: Bqbarcodegeneration
*  Description:  Generate and print UPC and EAN13 barcode products
*  @author Boutiquet.net
*  @copyright  Boutiquet.net
*  @version  Release: 1.0
*/

$(document).ready(function () {
    var resultat = $('#product_id');
    $('#categorie_id').live('change', function () {
        $.ajax({
            type: 'GET',
            data: {
                destination_id: $(this).val()
            },
            url: '../modules/bqbarcodegeneration/ajaxsearch.php',
            dataType: 'json',
            success: function (json) {
                $('#product_id option').remove();
                resultat.append('<option value=0 >--------------------</option>');
                $.each(json, function (index, value) { // existe aussi
                    resultat.append('<option value=' + json[index]['id_product'] + ' >' + json[index]['name'] + '</option>');
                });
            },
            error: function () {
                resultat.html('Erreur load data from database');
            }
        });
        return false;
    });

    //hiding tab content except first one
    $(".tabContent").not(":first").hide();
    // adding Active class to first selected tab and show 
    $("ul.tabs li:first").addClass("active").show();

    // Click event on tab
    $("ul.tabs li").click(function () {
        // Removing class of Active tab
        $("ul.tabs li.active").removeClass("active");
        // Adding Active class to Clicked tab
        $(this).addClass("active");
        // hiding all the tab contents
        $(".tabContent").hide();
        // showing the clicked tabs content using fading effect
        $($("a", this).attr("href")).fadeIn("slow")
        return false;
    });
	 
 
});