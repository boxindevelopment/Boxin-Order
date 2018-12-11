<!DOCTYPE html>
<html>
<head>
	<title>Hi</title>
	<style type="text/css">
		@import url('https://fonts.googleapis.com/css?family=Dosis:300,400,500,600,700');
		html {
			margin : 5px;
		}
		body {
			font-family: 'Dosis', sans-serif;
			font-weight: 400;
			font-size: 12px;
			margin: 10px;
		}
	</style>
</head>
<body>
	<div style="margin: 5px;">
		<div style=""><img width="52" height="26" src="{{asset('images/logo.png')}}" /></div>
		<br>
		<h3 style="font-weight: 700; font-size:16px; margin-bottom: 5px;">Invoice Order <span style="font-size: 18px;">#43243242343</span></h3>
		<p style=" margin-top: 2px;">9 december 2018</p>
		<h5 style="font-size:14px; font-weight: 600; margin-bottom: 2px;">Item</h5>
		<table width="100%" border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
			<tr>
				<td width="55" style="border-bottom: 1px solid #eee; margin-bottom: 5px; padding-bottom: 15px; padding-top:10px;">
					<img width="55" src="{{asset('images/product-dummy.png')}}" >
				</td>
				<td style="border-bottom: 1px solid #eee; padding-bottom: 15px; padding-top:10px;">
					<h5 style="margin-bottom: 5px; margin-top: 5px;">1 Medium Box</h5>
					<p style="margin-bottom: 5px; margin-top: 5px;">Rp 15.000 / day</p>
					<p style="margin-bottom: 5px; margin-top: 5px; margin-bottom: 5px;">14 days</p>
				</td>
				<td valign="bottom" style="width: 100px; padding-bottom: 15px; padding-top:10px; border-bottom: 2px solid #eee;"><p style="margin-bottom: 5px; margin-top: 5px; margin-bottom: 15px; text-align: right;">Rp 210.000</p></td>
			</tr>
			<tr>
				<td colspan="2" style="border-bottom: 1px solid #eee;">Delivery</td>
				<td style="border-bottom: 2px solid #eee; text-align: right; padding-bottom: 15px; padding-top:10px;">Rp 20.000</td>
			</tr>
			<tr>
				<td colspan="2" style="border-bottom: 1px solid #eee; padding-bottom: 15px;"><strong>Total</strong></td>
				<td style="border-bottom: 2px solid #eee; padding-bottom: 15px; text-align: right;"><strong>Rp. 220.000</strong></td>
			</tr>
		</table>
		<br><br><br>
		<p style="">Thank you for trusting us, Boxin. </p>
	</div>
</body>
</html>
