<?php

/*
 * The MIT License
 *
 * Copyright 2018 foraern.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

session_start();
include('lib/functions.php');
include('lib/calc.php');
$f = new functions();
$c = new calc();
if(!isset($_SESSION['uid']))
{
	header('location: /');
}
$f->getuserstats();
$f->getuser($_SESSION['uid']);
if(!empty($_POST['password']) && !empty($_POST['confirmpassword']) && $_POST['password'] == $_POST['confirmpassword'])
{
	$f->changepassword($_POST['password']);
}

$emailnotifications=!empty($_POST['emailnotifications'])?$_POST['emailnotifications']:"";
if(!empty($_POST['url']) && (!empty($_POST['dataorigin']) && $_POST['dataorigin']=="0") && filter_var($_POST['url'], FILTER_VALIDATE_URL) !== FALSE)
{
	$f->saveprofile($_POST['dataorigin'], $_POST['url'], $emailnotifications);
	$f->getuser($_SESSION['uid']);
}
elseif(!empty($_POST['dataorigin']) && $_POST['dataorigin']=="1")
{
	$f->saveprofile($_POST['dataorigin'], "", $emailnotifications);
	$f->getuser($_SESSION['uid']);
}

$contentdata["email"] = $f->user->email;
$contentdata["emailnotifications"] = $f->user->emailnotifications == 1 ? "checked" : "";
$contentdata["dataoriginethos"] = $f->user->dataorigin == 0 ? "checked='true'" : "";
$contentdata["dataoriginethosblock"] = $f->user->dataorigin == 0 ? "display:block" : "display:none";
$contentdata["dataoriginrig"] = $f->user->dataorigin == 1 ? "checked='true'" : "";
$contentdata["dataoriginrigblock"] = $f->user->dataorigin == 1 ? "display:block" : "display:none";
$contentdata["dataoriginrigtext"] = !empty($f->user->datahash) ? "<label>Direct to Rig Script</label>  (run the following command on each rig)<br /><code>(crontab -l 2>/dev/null; echo '0,10,20,30,40,50 * * * * curl -X POST -H \"Content-Type: multipart/form-data\" -F \"token=".$f->user->datahash."\" -F \"data=@/var/run/ethos/stats.json\" https://ethos-panel.com/post.php') | crontab -</code>" : "";
$contentdata["url"] = $f->user->url;
$contentdata["news"] = $f->getnews();
$contentdata["rigs"] = "(" . $f->countrigs() . " rigs and counting!)";
$total_hash = 0;
if(!empty($f->stats['rigs']))
{
	foreach($f->stats['rigs'] as $key => $value)
	{
		$hashes = explode(" ", $value['miner_hashes']);
		$total_hash += array_sum($hashes);
	}
}
$price = $c->getprice("Ethereum");
$profit = $c->geteth($total_hash);
$contentdata['profiteth'] = round($profit, 4);
$contentdata['profitbtc'] = round($profit * $price[0]->price_btc, 4);
$contentdata['profitusd'] = round($profit * $price[0]->price_usd, 4);
$contentdata['profiteur'] = round($profit * $price[0]->price_eur, 4);
if(!empty($f->config->analytics))
{
	$contentdata['trackingcode'] = $f->config->analytics;
}
echo $f->getcontent('./templates/header.html', $contentdata);
echo $f->getcontent('./templates/profile.html', $contentdata);
echo $f->getcontent('./templates/footer.html', $contentdata);

