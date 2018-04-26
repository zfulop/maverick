<!-- BEGIN BCR -->

<table style='max-width: 600px; width: 100%; font-family: Arial, Verdana; line-height: 1.3; font-size: 16px; border-spacing: 0px; border-collapse: collapse'>
    <tr>
        <td style='width: 50%; padding: 0px;'>
            <div style='background: #3f3f3f; height: 110px; text-align: left; padding-left: 15px; padding-top: 10px;'>
                <img src='cid:logo' alt='maverick-logo' style='width: 100%; max-width: 120px; max-height: 70px; margin-top: 15px;'>
            </div>
        </td>
        <td style="width: 50%; padding: 0px;">
             <div style='background: #3f3f3f; height: 110px; text-align: right; padding-right: 15px; padding-top: 10px; font-size: 25px;'>
                 <a style='color: #fff; text-decoration: none; margin-right: 5px;' href="https://www.facebook.com/mavericklodges"><img src='cid:facebook'></a>
                 <a style='color: #fff; text-decoration: none; margin-right: 5px;' href="https://plus.google.com/+MaverickHostel"><img src='cid:gplus'></a>
                 <a style='color: #fff; text-decoration: none;' href="https://www.instagram.com/mavericklodges/?hl=en"><img src='cid:insta'></a>
             </div>
        </td>
    </tr>
    <tr>
        <td colspan='2' style="padding: 0px;">
            <img src='cid:reservation' style="max-width: 100%">
        </td>
    </tr>
    <tr>
        <td colspan='2' style="background: #610668; color: #fff; font-weight: bold; height: 60px; padding: 20px;">
		    {bcr_message}
			{BELOW_FIND_BOOKING_INFO}
        </td>
    </tr>

    <tr>
        <td colspan='2' style="padding: 0px; padding-left: 15px; padding-top: 30px; padding-right: 15px;">
            
            <!----- RESERVATIONS DETAILS ----------------------------------------------------------------------------------------------------------->
            <div style="margin-bottom: 10px; padding-left: 10px;">
                <div style="float: left; min-width: 200px;  color: #727272; font-style: italic; font-weight: bold; font-size: 14px;">{NAME}</div>
                <div style="float: left; min-width: 200px; color: #000; font-weight: bold; font-size: 14px;">{booker_name}</div>
                <div style='clear: both'></div>
            </div>
            
            <div style="margin-bottom: 10px; padding-left: 10px;">
                <div style="float: left; min-width: 200px; color: #727272; font-style: italic; font-weight: bold; font-size: 14px;">{EMAIL}</div>
                <div style="float: left; min-width: 200px; color: #000; font-weight: bold; font-size: 14px;">{booker_email}</div>
                <div style='clear: both'></div>
            </div>
            
            <div style="margin-bottom: 10px; padding-left: 10px;">
                <div style="float: left; min-width: 200px; color: #727272; font-style: italic; font-weight: bold; font-size: 14px;">{PHONE}</div>
                <div style="float: left; min-width: 200px; color: #000; font-weight: bold; font-size: 14px;">{booker_phone}</div>
                <div style='clear: both'></div>
            </div>
            
            <div style="margin-bottom: 10px; padding-left: 10px;">
                <div style="float: left; min-width: 200px;  color: #727272; font-style: italic; font-weight: bold; font-size: 14px;">{ADDRESS_TITLE}</div>
                <div style="float: left; min-width: 200px; color: #000; font-weight: bold; font-size: 14px;">{booker_address}</div>
                <div style='clear: both'></div>
            </div>
            
            <div style="margin-bottom: 10px; padding-left: 10px;">
                <div style="float: left; min-width: 200px;  color: #727272; font-style: italic; font-weight: bold; font-size: 14px;">{NATIONALITY}</div>
                <div style="float: left; min-width: 200px; color: #000; font-weight: bold; font-size: 14px;">{booker_nationality}</div>
                <div style='clear: both'></div>
            </div>
            
            <div style="margin-bottom: 10px; padding-left: 10px;">
                <div style="float: left; min-width: 200px;  color: #727272; font-style: italic; font-weight: bold; font-size: 14px;">{DATE_OF_ARRIVAL}</div>
                <div style="float: left; min-width: 200px; color: #000; font-weight: bold; font-size: 14px;">{booker_arrival_date}</div>
                <div style='clear: both'></div>
            </div>
            
            <div style="margin-bottom: 10px; padding-left: 10px;">
                <div style="float: left; min-width: 200px;  color: #727272; font-style: italic; font-weight: bold; font-size: 14px;">{DATE_OF_DEPARTURE}</div>
                <div style="float: left; min-width: 200px; color: #000; font-weight: bold; font-size: 14px;">{booker_departure_date}</div>
                <div style='clear: both'></div>
            </div>
            
            <div style="margin-bottom: 10px; padding-left: 10px;">
                <div style="float: left; min-width: 200px;  color: #727272; font-style: italic; font-weight: bold; font-size: 14px;">{NUMBER_OF_NIGHTS}</div>
                <div style="float: left; min-width: 200px; color: #000; font-weight: bold; font-size: 14px;">{booker_number_of_nights}</div>
                <div style='clear: both'></div>
            </div>
            
            <div style="margin-bottom: 10px; padding-left: 10px;">
                <div style="float: left; min-width: 200px;  color: #727272; font-style: italic; font-weight: bold; font-size: 14px;">{comment}</div>
                <div style="float: left; min-width: 200px; color: #000; font-weight: bold; font-size: 14px;">{booker_comment}</div>
                <div style='clear: both'></div>
            </div>
            
        </td>
    </tr>

<!-- BEGIN item_block -->
    <tr>
        <td colspan='2' style="padding: 0px; ">
			<div style="margin-bottom: 10px; padding-left: 10px; padding-top: 20px;">
                <div style="color: #727272; font-style: italic; font-weight: bold; font-size: 16px;">{item_title}</div>
            </div>
            <hr style="border-bottom: 1px; border-top: 1px solid #b9b9b9;">
<!-- BEGIN item -->
            <div style=" margin-top: 15px; padding-left: 15px;">
                <div style="float: left; min-width: 180px; color: #727272; font-style: italic; font-weight: bold; font-size: 14px;">{item_name}</div>
                <div style="float: right; min-width: 50px; color: #000; font-weight: bold; text-transform: uppercase; font-size: 14px; text-align: right; padding-right: 15px;">{item_price}</div>
                <div style='clear: both'></div>
            </div>
<!-- END item -->
            <hr style="border-bottom: 1px; border-top: 1px solid #b9b9b9;">
        </td>
	</tr>
<!-- END item_block -->

    <tr>
        <td colspan='2' style="padding: 0px; ">

			<!----- TOTAL --------------------------------------------------------------------------------------------------------------->
            <div style="background: #b9b9b9; min-height: 55px;  margin-top: 10px; margin-bottom: 10px;">
                <div style="float: left; min-width: 140px;  color: #fff; font-style: italic; text-transform: uppercase; font-size: 12x; padding-left: 15px; padding-top: 25px;">{TOTAL_PRICE}</div>
<!-- BEGIN total_payment -->
                <div style="float: right; min-width: 130px; color: #fff; font-weight: bold; font-size: 32px; padding-top: 10px; text-align: right; padding-right: 15px;">{booker_totalprice}</div>
<!-- END total_payment -->
                <div style='clear: both'></div>
            </div>
            
        </td>
    </tr>
    
    <!----- Advise to travel ------------------------------------------------------------------------------------------------------------->
    <tr>
        <td colspan='2' style="padding: 0px;">
                <div style="color: #252525; font-weight: bold; font-size: 14px; padding-top: 10px; text-align: left; padding-right: 15px;">{ADVISE_TO_TRAVEL}</div>
        </td>
    </tr>

    <!----- MAP ------------------------------------------------------------------------------------------------------------->
    <tr>
        <td colspan='2' style="padding: 0px;">
		      <img style="max-width: 100%; margin-top: 30px;" src="cid:map">
        </td>
    </tr>

    <!----- Getting here --------------------------------------------------------------------------------------------------------->

    <tr>
        <td colspan='2' style="padding: 0px;">
		
			<table width="100%" cellspacing="0" border="0" cellpadding="0">
				<!-- space --><tr><td height="20"></td></tr>
				<tr>
					<td><img width="49" height="49" src="cid:railwaystation"></td>
				</tr>
				<!-- space --><tr><td height="10"></td></tr>
				<tr>
					<td><font face="arial" color="#252525" style="font-size: 14px;"><b>{RAILWAY_STATIONS}</b></font></td>
				</tr>
				<!-- space --><tr><td height="10"></td></tr>
				<tr>
					<td>
						<table width="100%" cellspacing="0" border="0" cellpadding="0">
							<tr>
								<td width="15"></td>
								<td width="6" bgcolor="#959595"></td>
								<td width="10"></td>
								<td width="100%">
									<font face="arial" color="#252525" style="font-size: 14px;">{fromTrainStationInstr}</font>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<!-- space --><tr><td height="20"></td></tr>
				<tr>
					<td><img width="54" height="55" src="cid:airport"></td>
				</tr>
				<!-- space --><tr><td height="10"></td></tr>
				<tr>
					<td><font face="arial" color="#252525" style="font-size: 14px;"><b>{FROM_AIRPORT}</b></font></td>
				</tr>
				<!-- space --><tr><td height="10"></td></tr>
				<tr>
					<td>
						<table width="100%" cellspacing="0" border="0" cellpadding="0">
							<tr>
								<td width="15"></td>
								<td width="6" bgcolor="#959595"></td>
								<td width="10"></td>
								<td width="100%"><font face="arial" color="#252525" style="font-size: 14px;">{fromAirportInstr}</font></td>
							</tr>
						</table>
					</td>
				</tr>
				<!-- space --><tr><td height="20"></td></tr>
				<tr>
					<td>
						<table width="100%" cellspacing="0" border="0" cellpadding="0">
							<tr>
								<td width="15"></td>
								<td width="6" bgcolor="#959595"></td>
								<td width="10"></td>
								<td width="100%"><font face="arial" color="#252525" style="font-size: 14px;">{fromAirportInstr2}</font></td>
							</tr>
						</table>
					</td>
				</tr>
				<!-- space --><tr><td height="35"></td></tr>
				<tr>
					<td>
						<font face="arial" color="#252525" style="font-size: 14px;">
						{PAYMENT_DESCRIPTION}<br>
						{ACTUAL_EXCHANGE_RATE}:
						<a href="http://www.cib.hu/maganszemelyek/arfolyamok/arfolyamok"><font color="#101010">http://www.cib.hu/maganszemelyek/arfolyamok/arfolyamok</font></a>
						</font>
					</td>
				</tr>
				<!-- space --><tr><td height="35"></td></tr>
				<tr>
					<td><font face="arial" color="#252525" style="font-size: 25px; line-height: 1.2;">{POLICY}</font></td>
				</tr>
				<!-- space --><tr><td height="20"></td></tr>
				<tr>
					<td>
						<table width="100%" cellspacing="0" border="0" cellpadding="0">
	<!-- BEGIN policy -->
							<tr>
								<td width="15" valign="top"><img width="5" height="17" src="cid:bullet"></td>
								<td><font face="arial" color="#252525" style="font-size: 14px; line-height: 1.2;">{policy_text}</font></td>
							</tr>
							<!-- space --><tr><td height="10"></td></tr>
	<!-- END policy -->
						</table>
					</td>
				</tr>
				<!-- space --><tr><td height="35"></td></tr>
			</table>
				</td>
				<td></td>
				</tr>
			</table>

        </td>
    </tr>

		
<!-- FOOTER -------------------------------------------------------------------------------------------------------------------------------->

<table style='max-width: 600px; width: 100%; font-family: Arial, Verdana; line-height: 1.3; font-size: 16px; border-spacing: 0px; margin-top: 30px;'>
    <tr>
        <td style='background: #3f3f3f; padding: 25px; color: #fff'>
            <table class='mvCol1' style='width: 100%'>
                <tr>
                    <td style='vertical-align: top'>
                        <table class='mvCol1' style='width: 100%; max-width: 300px; float: left;'>
                            <tr>
                                <td style='width: 100%'>
                                    <h3 style='color: #fff; margin-top: 0px;'>Maverick Lodges</h3>
                                    <div style='line-height: 30px;'><a href='https://mavericklodges.com/city-lodge-budapest-hostel/' target='_blank' style='color: #fff; text-decoration: none; font-size: 14px;'>MAVERICK CITY LODGE</a></div>
                                    <div style='line-height: 30px;'><a href='https://mavericklodges.com/hostel-budapest/' target='_blank' style='color: #fff; text-decoration: none; font-size: 14px;'>MAVERICK HOSTEL</a></div>
                                    <div style='line-height: 30px;'><a href='https://mavericklodges.com/apartments-budapest/' target='_blank' style='color: #fff; text-decoration: none; font-size: 14px;'>MAVERICK APARTMENTS</a></div>
                                    <div style='line-height: 30px;'><a href='http://fatmama.hu/eng/' target='_blank' style='color: #fff; text-decoration: none; font-size: 14px;'>RESTAURANT</a></div>
                                </td>
                            </tr>
                        </table>
                        <table style='width: 100%; max-width: 300px; float: left;'>
                            <tr>
                                <td style='width: 100%'>
                                    <div style='color: #acacac; font-size: 12px; line-height: 20px'>Hostel</div>
                                    <div style='color: #fff; font-size: 12px; line-height: 20px'><span style='color: #acacac; font-size: 12px;'>Call Us</span> +36 1 2673166</div>
                                    <div style='color: #fff; font-size: 12px; line-height: 20px'><span style='color: #acacac; font-size: 12px;'>Email</span> <a style='color: #fff; text-decoration: none' href="mailto:reservation@maverickhostel.com">reservation@maverickhostel.com</a></div>
                                    <br><br>
                                    <div style='color: #acacac; font-size: 12px; line-height: 20px'>Lodge</div>
                                    <div style='color: #fff; font-size: 12px; line-height: 20px'><span style='color: #acacac; font-size: 12px;'>Call Us</span> +36 1 7931605</div>
                                    <div style='color: #fff; font-size: 12px; line-height: 20px'><span style='color: #acacac; font-size: 12px;'>Email</span> <a style='color: #fff; text-decoration: none' href="mailto:reservation@mavericklodges.com">reservation@mavericklodges.com</a></div>

                                    <br><br>
                                    <a style='color: #fff; text-decoration: none' href="https://www.facebook.com/mavericklodges" target="_blank"><i class='fa fa-fw fa-facebook'></i></a>&nbsp;&nbsp;
                                    <a style='color: #fff; text-decoration: none' href="https://plus.google.com/+MaverickHostel/posts" target="_blank"><i class='fa fa-fw fa-google-plus'></i></a>
                                </td>
                            </tr>
                        </table>

                        <table style='width: 100%; max-width: 120px; float: left;'>
                            <tr>
                                <td style='width: 100%'>
                                    <a href="http://www.famoushostels.com/best-hostels/" target="_blank"><img src="cid:famous_hostels" alt='famous'></a>
                                </td>
                            </tr>
                        </table>

                        <div style='clear: both'></div>
                        <table  style=' width: 100%'>
                            <tr>
                                <td style='width: 100%'>
                                    <h3 style='color: #fff'>Awards</h3>

                                    <div style='color: #fff; font-size: 12px; line-height: 20px'><span style='color: #acacac; font-size: 12px;'>MAVERICK CITY LODGE</span></div>

                                    <div style='float: left; width: 85px; text-align: center'><img alt='award' src="cid:booking_award_footer_2016" height="70" width="64" style="margin:10px 0px"></div>
                                    <div style='float: left; width: 85px; text-align: center'><img alt='award' src="cid:5star_award_footer_2016" height="70" width="56" style="margin:10px 0px"></div>
                                    <div style='float: left; width: 85px; text-align: center'><img alt='award' src="cid:5star_award_footer_2015" height="70" width="56" style="margin:10px 0px"></div>
                                    <div style='float: left; width: 85px; text-align: center'><img alt='award' src="cid:tripadvisor_award_footer_2015" height="70" width="68" style="margin:10px 0px"></div>
                                    <div style='float: left; width: 85px; text-align: center'><img alt='award' src="cid:hostelworld_award_footer_2015" height="70" width="70" style="margin:10px 0px"></div>
                                    <div style='clear: both'></div>
                                    <div style='color: #fff; font-size: 12px; line-height: 20px'><span style='color: #acacac; font-size: 12px;'>MAVERICK HOSTEL</span></div>
                                    <div style='float: left; width: 85px; text-align: center'><img alt='award' src="cid:tripadvisor_award_footer_2016" height="70" width="68" style="margin:10px 0px"></div>
                                    <div style='float: left; width: 85px; text-align: center'><img alt='award' src="cid:tripadvisor_award_footer_2015" height="70" width="68" style="margin:10px 0px"></div>
                                    <div style='float: left; width: 85px; text-align: center'><img alt='award' src="cid:tripadvisor_award_footer_2014" height="70" width="68" style="margin:10px 0px"></div>
                                    <div style='float: left; width: 85px; text-align: center'><img alt='award' src="cid:tripadvisor_award_footer_2013" height="70" width="68" style="margin:10px 0px"></div>
                                    <div style='float: left; width: 85px; text-align: center'><img alt='award' src="cid:hostelbookers_award_footer_2013" height="70" width="48" style="margin:10px 0px"></div>
                                    <div style='float: left; width: 85px; text-align: center'><img alt='award' src="cid:tripadvisor_award_footer_2012" height="70" width="68" style="margin:10px 0px"></div>
                                    <div style='float: left; width: 85px; text-align: center'><img alt='award' src="cid:hostelbookers_award_footer_2012" height="70" width="48" style="margin:10px 0px"></div>
                                    <div style='clear: both'></div>

                                </td>
                            </tr>
                        </table>
                        <div style='clear: both'></div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- END BCR -->