<?php
	$Parent_Url = "pages/sales/tcart.php";

	$Explode_Sender 	 = explode('--', $_POST['cmbOrign']);
	$Explode_Shipping_ID = $Explode_Sender[0];
	$Explode_Sender_ID   = $Explode_Sender[1];
	$Explode_Sender_Name = $Explode_Sender[2];

	$kota_asal 	 = $Explode_Sender_ID;
	$kota_tujuan = $_POST['txtDestinationID'];
	$kurir 		 = $_POST['cmbCourier'];
	$berat  	 = $_POST['txtWeight']*1000;

	$curl = curl_init();
	curl_setopt_array($curl, array(
	  CURLOPT_URL => "http://api.rajaongkir.com/starter/cost",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => "origin=".$kota_asal."&destination=".$kota_tujuan."&weight=".$berat."&courier=".$kurir."",
	  CURLOPT_HTTPHEADER => array(
	    "content-type: application/x-www-form-urlencoded",
	    "key: 416d06e16fe66d2779966d7505de28f4"
	  ),
	));
	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);
	$data= json_decode($response, true);
	$kurir=$data['rajaongkir']['results'][0]['name'];
	$kotaasal=$data['rajaongkir']['origin_details']['city_name'];
	$provinsiasal=$data['rajaongkir']['origin_details']['province'];
	$kotatujuan=$data['rajaongkir']['destination_details']['city_name'];
	$provinsitujuan=$data['rajaongkir']['destination_details']['province'];
	$berat=$data['rajaongkir']['query']['weight']/1000;

	$Action = "pages/sales/xcart_courier.php?act=edtcrt&hisid=".$_GET['hisid']."&shpid=".$Explode_Shipping_ID;
?>

<form class="form-horizontal" id="myForm" method="POST" action="<?PHP echo $Action; ?>" >
	<div class="panel panel-default">
		<div class="panel-body">
			<table width="100%">
			<tr>
				<td width="15%"><b>Kurir</b> </td>
				<td>&nbsp;<b><?=$kurir?></b></td>
			</tr>
			<tr>
				<td>Dari</td>
				<td>: <?=$kotaasal.", ".$provinsiasal?></td>
			</tr>
			<tr>
				<td>Tujuan</td>
				<td>: <?=$kotatujuan.", ".$provinsitujuan?></td>
			</tr>
			<tr>
				<td>Berat (Kg)</td>
				<td>: <?=$berat?></td>
			</tr>
			</table><br>
			<table class="table table-striped table-bordered ">
				<thead>
		  		<tr>
		  			<th>Nama Layanan</th>
		  			<th>Tarif</th>
		  			<th>ETD(Estimates Days)</th>
		  			<th>Pilih Layanan</th>
		  		</tr>
				</thead>
				<tbody>
		  		<?php
		  			foreach ($data['rajaongkir']['results'][0]['costs'] as $value) {
		  				echo "<tr>";
		  				echo "<td>".$value['service']."</td>";

		  				foreach ($value['cost'] as $tarif) {
		  					echo "<td align='right'>Rp " . number_format($tarif['value'],2,',','.')."</td>";
		  					echo "<td>".$tarif['etd']."</td>";
		  				}
		  				?>
		  				<td style="cursor: pointer;">
			  				<input type="radio" id="rdoCourierService_<?PHP echo $value['service']; ?>" name="rdoCourierService" 
			                    value="<?PHP echo $_POST['cmbCourier']; ?>--<?PHP echo $value['service']; ?>--<?PHP echo $tarif['value']; ?>"style="cursor: pointer;" >
			                <label for="rdoCourierService_<?PHP echo $value['service']; ?>" style="font-weight: normal; cursor: pointer;"><?PHP echo $value['service']; ?></small>
			                </label>
			            </td>
		  				<?PHP
		  				echo "</tr>";
		  			}
		  		?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="modal-footer justify-content-between" align="center">
		<button type="submit" class="btn btn-success float-sm-right"> 
			<i class="fa fa-truck menu-icon"></i> Update Kurir</button>
	</div>
</form>

<script type="text/javascript">
  $("#myForm").submit(function(event){ 
    toastr.options = {
      "debug": false,
      "positionClass": "toast-top-center",
      "onclick": null,
      "fadeIn": 300,
      "fadeOut": 1000,
      "timeOut": 5000,
      "extendedTimeOut": 1000,
      "closeButton": true
    }

    event.preventDefault(); //prevent default action 
    var post_url        = $(this).attr("action"); //get form action url
    var request_method  = $(this).attr("method"); //get form GET/POST method
    var form_data       = $(this).serialize(); //Encode form elements for submission    
    $.ajax({
      url : post_url,
      type: request_method,
      data : form_data,
      beforeSend:function(){$(".spinner").css("display","block");}
    }).done(function(response){
      if(response.indexOf('Berhasil') > -1){
        $("#modal-add").modal("hide");
        toastr.success(response,'Konfirmasi');       
        $('#rightcolumn').load("<?PHP echo $Parent_Url ; ?>");
        return false;
      } 
      else{
        toastr.error(response,'Konfirmasi');
        $(".spinner").css("display","none");
      }
    });     
  });
</script>