<?php

include_once("fpdf/fpdf.php");


// Xavier Nicolay 2004
// Version 1.02

//////////////////////////////////////
// Public functions                 //
//////////////////////////////////////
//  function sizeOfText( $texte, $larg )
//  function addSociete( $nom, $adresse )
//  function fact_dev( $libelle, $num )
//  function addDevis( $numdev )
//  function addFacture( $numfact )
//  function addDate( $date )
//  function addClient( $ref )
//  function addPageNumber( $page )
//  function addClientAdresse( $adresse )
//  function addReglement( $mode )
//  function addEcheance( $date )
//  function addNumTVA($tva)
//  function addReference($ref)
//  function addCols( $tab )
//  function addLineFormat( $tab )
//  function lineVert( $tab )
//  function addLine( $ligne, $tab )
//  function addRemarque($remarque)
//  function addCadreTVAs()
//  function addCadreEurosFrancs()
//  function addTVAs( $params, $tab_tva, $invoice )
//  function temporaire( $texte )

class PDF_Invoice extends FPDF
{
// private variables
var $colonnes;
var $format;
var $angle=0;

// private functions
function RoundedRect($x, $y, $w, $h, $r, $style = '')
{	
    $k = $this->k;
    $hp = $this->h;
    if($style=='F')
        $op='f';
    elseif($style=='FD' || $style=='DF')
        $op='B';
    else
        $op='S';
    $MyArc = 4/3 * (sqrt(2) - 1);
    $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
    $xc = $x+$w-$r ;
    $yc = $y+$r;
    $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));

    $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
    $xc = $x+$w-$r ;
    $yc = $y+$h-$r;
    $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
    $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
    $xc = $x+$r ;
    $yc = $y+$h-$r;
    $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
    $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
    $xc = $x+$r ;
    $yc = $y+$r;
    $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
    $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
    $this->_out($op);
	
}

function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
{
    $h = $this->h;
    $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
                        $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
}

function Rotate($angle, $x=-1, $y=-1)
{
    if($x==-1)
        $x=$this->x;
    if($y==-1)
        $y=$this->y;
    if($this->angle!=0)
        $this->_out('Q');
    $this->angle=$angle;
    if($angle!=0)
    {
        $angle*=M_PI/180;
        $c=cos($angle);
        $s=sin($angle);
        $cx=$x*$this->k;
        $cy=($this->h-$y)*$this->k;
        $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
    }
}

function _endpage()
{
    if($this->angle!=0)
    {
        $this->angle=0;
        $this->_out('Q');
    }
    parent::_endpage();
}

// public functions
function sizeOfText( $texte, $largeur )
{
    $index    = 0;
    $nb_lines = 0;
    $loop     = TRUE;
    while ( $loop )
    {
        $pos = strpos($texte, "\n");
        if (!$pos)
        {
            $loop  = FALSE;
            $ligne = $texte;
        }
        else
        {
            $ligne  = substr( $texte, $index, $pos);
            $texte = substr( $texte, $pos+1 );
        }
        $length = floor( $this->GetStringWidth( $ligne ) );
        $res = 1 + floor( $length / $largeur) ;
        $nb_lines += $res;
    }
    return $nb_lines;
}

function AddLogo($url){


$x1= 10;
$y1 = 6;

$this->Image($url,$x1,$y1,40,25);

}

function AddImages($images){
	
	$x1=10;
	$y1=10;
	
	$this->AddPage();
	$count=0;
	foreach ($images as &$image){
		
	if($count > 1){
		
		$this->AddPage();
		$count=0;
		$y1=10;
	}
		
	list($width, $height) = getimagesize($image);
	
	$this->Image($image,$x1,$y1,191,123);
	
	$y1=140;	
	
	$count=$count+1;
	
		
	}
	
	
}

// Company
function AddCompany( $name, $address )
{
	if ($address!=""){
	$this->SetFont('Arial','B',12);
	$length = $this->GetStringWidth( $name );
    $x1=9;
    $y1 = 34;
    $this->SetXY( $x1, $y1 );
	$this->Cell($length,2,$name);
    

    $this->SetXY( $x1, $y1 + 4 );
    $this->SetFont('Arial','',10);
    $length = $this->GetStringWidth( $address );
    $lignes = $this->sizeOfText( $address, $length) ;
	
    $this->MultiCell($length, 4, $address);
}
}

function AddDocumentName($name){

$this->SetFont('Arial','B',27);
$length = $this->GetStringWidth($name);
$x = ($this->w/2)-($length/2);
$y = 10;


$this->SetXY($x,$y);

$this->Cell($length,2,$name);


}

// Label and number of invoice/estimate
/* function fact_dev( $libelle, $num )
{
    $r1  = $this->w - 80;
    $r2  = $r1 + 68;
    $y1  = 6;
    $y2  = $y1 + 2;
    $mid = ($r1 + $r2 ) / 2;
    
    $texte  = $libelle . " EN " . EURO . " N� : " . $num;    
    $szfont = 12;
    $loop   = 0;

    while ( $loop == 0 )
    {

       $this->SetFont( "Arial", "B", $szfont );
       $sz = $this->GetStringWidth( $texte );
       if ( ($r1+$sz) > $r2 )
          $szfont --;
       else
          $loop ++;
    }

    $this->SetLineWidth(0.1);
    $this->SetFillColor(192);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 2.5, 'DF');
    $this->SetXY( $r1+1, $y1+2);
    $this->Cell($r2-$r1 -1,5, $texte, 0, 0, "C" );
} */

// Estimate
function addDevis( $numdev )
{
    $string = sprintf("DEV%04d",$numdev);
    $this->fact_dev( "Devis", $string );
}

// Invoice
function addFacture( $numfact )
{
    $string = sprintf("FA%04d",$numfact);
    $this->fact_dev( "Facture", $string );
}

function addDate( $date )
{
	
	
	$r1  = $this->w - 61;
    $r2  = $r1 + 30-5;
    $y1  = 14;
    $y2  = 8 ;
    $mid = $y1 + ($y2 / 2);

    $this->Rect($r1, $y1, ($r2 - $r1), $y2);
	
	
    $r1  = $this->w - 61;
    $r2  = $r1 + 30-5;
    $y1  = 6;
    $y2  = 8 ;
    $mid = $y1 + ($y2 / 2);
	
	$this->SetFillColor(192,192,192);
    $this->Rect($r1, $y1, ($r2 - $r1), $y2, 'DF');

	
	    
  
	
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
    $this->SetFont( "Arial", "B", 10);
	$this->SetTextColor(0,0,0);
    $this->Cell(10,5, "DATE", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+9 );
    $this->SetFont( "Arial", "", 10);
	$this->SetTextColor(100,100,100);
    $this->Cell(10,5,$date, 0,0, "C");
}

function addClient( $ref,$doctype )
{
		
	$r1  = $this->w - 31;
    $r2  = $r1 + 19;
    $y1  = 14;
    $y2  = 8;
    $mid = $y1 + ($y2 / 2);
    $this->Rect($r1-5, $y1, ($r2 - $r1+7), $y2);
		
		
		
		
	
    $r1  = $this->w - 31;
    $r2  = $r1 + 19;
    $y1  = 6;
    $y2  = 8;
    $mid = $y1 + ($y2 / 2);
    $this->SetFillColor(192,192,192);
    $this->Rect($r1-5, $y1, ($r2 - $r1+7), $y2, 'DF');
    $this->SetXY( $r1 + ($r2-$r1-5)/2 - 5, $y1+3 );
    $this->SetFont( "Arial", "B", 10);
	$this->SetTextColor(0,0,0);
    $this->Cell(10,5, $doctype." #", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1-5)/2 - 5, $y1 + 9 );
    $this->SetFont( "Arial", "", 10);
	$this->SetTextColor(100,100,100);
    $this->Cell(10,5,$ref, 0,0, "C");
}


// Client address
function addClientInfo($name, $address, $title)
{	
	$x1 = $this->w - 62;
	
	$y1 = 25;
	
	//Positionnement en bas
    $this->SetXY( $x1, $y1 );
    $this->SetFont('Arial','',12);
	$myy = 0;
    $length = $this->GetStringWidth($title);
	while($length<52){
		
		$length=round($length+1);
		$myy=4;
	}

	if($length >52){$length = 52; $myy=7;}
	$this->MultiCell($length, 3, $title,0,'L');
	
    $y1 = 36;
	
    //Positionnement en bas
    $this->SetXY( $x1, $y1 );
    $this->SetFont('Arial','B',12);
	$myy = 0;
    $length = $this->GetStringWidth($name);
	while($length<52){
		
		$length=round($length+1);
		$myy=4;
	}

	if($length >52){$length = 52; $myy=7;}
	$this->MultiCell($length, 3, $name,0,'L');
    $this->SetXY( $x1, $y1 + $myy );
    $this->SetFont('Arial','',10);
    $length = $this->GetStringWidth( $address );
	
	while($length<52){
		$length=round($length+1);
	}
	if($length >52){$length = 52;}
	
    $this->MultiCell($length, 4, $address);
	
}

// Mode of payment
/*
function addReglement( $mode )
{
    $r1  = 10;
    $r2  = $r1 + 60;
    $y1  = 80;
    $y2  = $y1+10;
    $mid = $y1 + (($y2-$y1) / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 -5 , $y1+1 );
    $this->SetFont( "Arial", "B", 10);
    $this->Cell(10,4, "MODE DE REGLEMENT", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 -5 , $y1 + 5 );
    $this->SetFont( "Arial", "", 10);
    $this->Cell(10,5,$mode, 0,0, "C");
}*/

// Expiry date
function addExpirationDate( $date )
{
	$r1  = $this->w - 50;
    $r2  = $r1 + 40;
    $y1  = 65;
    $y2  = $y1+5;
    $mid = $y1 + (($y2-$y1) / 2);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1));
	
    $r1  = $this->w - 50;
    $r2  = $r1 + 40;
    $y1  = 60;
    $y2  = $y1+5;
    $mid = $y1 + (($y2-$y1) / 2);
    $this->SetFillColor(192,192,192);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    $this->SetXY( $r1 + ($r2 - $r1)/2 - 5 , $y1+1 );
    $this->SetFont( "Arial", "B", 10);
	$this->SetTextColor(0,0,0);
    $this->Cell(10,4, "Expiration Date", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5 , $y1 + 5 );
	$this->SetTextColor(100,100,100);
    $this->SetFont( "Arial", "", 10);
    $this->Cell(10,5,$date, 0,0, "C");
}

// VAT number
/*
function addNumTVA($tva)
{
    $this->SetFont( "Arial", "B", 10);
    $r1  = $this->w - 80;
    $r2  = $r1 + 70;
    $y1  = 80;
    $y2  = $y1+10;
    $mid = $y1 + (($y2-$y1) / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + 16 , $y1+1 );
    $this->Cell(40, 4, "TVA Intracommunautaire", '', '', "C");
    $this->SetFont( "Arial", "", 10);
    $this->SetXY( $r1 + 16 , $y1+5 );
    $this->Cell(40, 5, $tva, '', '', "C");
}
*/
/*
function addReference($ref)
{
    $this->SetFont( "Arial", "", 10);
    $length = $this->GetStringWidth( "R�f�rences : " . $ref );
    $r1  = 10;
    $r2  = $r1 + $length;
    $y1  = 92;
    $y2  = $y1+5;
    $this->SetXY( $r1 , $y1 );
    $this->Cell($length,4, "R�f�rences : " . $ref);
}*/

function addCols( $tab )
{
    global $colonnes;
    
	
	$r1  = 10;
    $r2  = $this->w - ($r1 * 2) ;
    $y1  = 72;
    $y2  = 6;
    $this->Rect( $r1, $y1, $r2, $y2, "DF");
	
    $r1  = 10;
    $r2  = $this->w - ($r1 * 2) ;
    $y1  = 72;
    $y2  = $this->h - 43- $y1;
    $this->SetXY( $r1, $y1 );
	$this->SetTextColor(0,0,0);
    $this->Rect( $r1, $y1, $r2, $y2);
    $colX = $r1;
    $colonnes = $tab;
    while ( list( $lib, $pos ) = each ($tab) )
    {
        $this->SetXY( $colX, $y1+3 );
        $this->Cell( $pos, 1, $lib, 0, 0, "C");
        $colX += $pos;
        $this->Line( $colX, $y1, $colX, $y1+$y2);
    }
}

function addLineFormat( $tab )
{
    global $format, $colonnes;
    
    while ( list( $lib, $pos ) = each ($colonnes) )
    {
        if ( isset( $tab["$lib"] ) )
            $format[ $lib ] = $tab["$lib"];
    }
}

function lineVert( $tab )
{
    global $colonnes;

    reset( $colonnes );
    $maxSize=0;
    while ( list( $lib, $pos ) = each ($colonnes) )
    {
        $texte = $tab[ $lib ];
        $longCell  = $pos -2;
        $size = $this->sizeOfText( $texte, $longCell );
        if ($size > $maxSize)
            $maxSize = $size;
    }
    return $maxSize;
}

// add a line to the invoice/estimate
/*    $ligne = array( "REFERENCE"    => $prod["ref"],
                      "DESIGNATION"  => $libelle,
                      "QUANTITE"     => sprintf( "%.2F", $prod["qte"]) ,
                      "P.U. HT"      => sprintf( "%.2F", $prod["px_unit"]),
                      "MONTANT H.T." => sprintf ( "%.2F", $prod["qte"] * $prod["px_unit"]) ,
                      "TVA"          => $prod["tva"] );
*/
function addLine( $ligne, $tab )
{
    global $colonnes, $format;

    $ordonnee     = 10;
    $maxSize      = $ligne;

    reset( $colonnes );
	
    while ( list( $lib, $pos ) = each ($colonnes) )
    {
	

        $longCell  = $pos -2;
        $texte     = $tab[ $lib ];
        $length    = $this->GetStringWidth( $texte );
		if($length==0){
			
			$length=1;
			
		}
        $tailleTexte = $this->sizeOfText( $texte, $length );
        $formText  = $format[ $lib ];
        $this->SetXY( $ordonnee, $ligne-1);
		$this->MultiCell( $longCell, 4 , $texte, 0, $formText,false);
        
        if ( $maxSize < ($this->GetY()  ) )
            $maxSize = $this->GetY() ;
        $ordonnee += $pos;

    }
	
	
    return ( $maxSize - $ligne );
}

function addRemarque($remarque)
{
    $this->SetFont( "Arial", "", 10);
    $length = $this->GetStringWidth( "Remarque : " . $remarque );
    $r1  = 10;
    $r2  = $r1 + $length;
    $y1  = $this->h - 45.5;
    $y2  = $y1+5;
    $this->SetXY( $r1 , $y1 );
    $this->Cell($length,4, "Remarque : " . $remarque);
}

function addCadreTVAs()
{
    $this->SetFont( "Arial", "B", 8);
    $r1  = 10;
    $r2  = $r1 + 120;
    $y1  = $this->h - 40;
    $y2  = $y1+20;
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1, $y1+4, $r2, $y1+4);
    $this->Line( $r1+5,  $y1+4, $r1+5, $y2); // avant BASES HT
    $this->Line( $r1+27, $y1, $r1+27, $y2);  // avant REMISE
    $this->Line( $r1+43, $y1, $r1+43, $y2);  // avant MT TVA
    $this->Line( $r1+63, $y1, $r1+63, $y2);  // avant % TVA
    $this->Line( $r1+75, $y1, $r1+75, $y2);  // avant PORT
    $this->Line( $r1+91, $y1, $r1+91, $y2);  // avant TOTAUX
    $this->SetXY( $r1+9, $y1);
    $this->Cell(10,4, "BASES HT");
    $this->SetX( $r1+29 );
    $this->Cell(10,4, "REMISE");
    $this->SetX( $r1+48 );
    $this->Cell(10,4, "MT TVA");
    $this->SetX( $r1+63 );
    $this->Cell(10,4, "% TVA");
    $this->SetX( $r1+78 );
    $this->Cell(10,4, "PORT");
    $this->SetX( $r1+100 );
    $this->Cell(10,4, "TOTAUX");
    $this->SetFont( "Arial", "B", 6);
    $this->SetXY( $r1+93, $y2 - 8 );
    $this->Cell(6,0, "H.T.   :");
    $this->SetXY( $r1+93, $y2 - 3 );
    $this->Cell(6,0, "T.V.A. :");
}


function addTotalsFormatting()
{
    $r1  = $this->w - 70;
    $r2  = $r1 + 60;
    $y1  = $this->h - 40;
    $y2  = $y1+13;
	$this->SetFillColor(192,192,192);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1));
	$this->SetTextColor(0,0,0);
    $this->SetFont( "Arial", "B", 7);
    $this->SetXY( $r1, $y1+4 );
    $this->Cell(20,4, "SUBTOTAL", 0, 0, "L");
    $this->SetXY( $r1, $y1+8 );
    $this->Cell(20,4, "TAXES", 0, 0, "L");
    $this->SetXY( $r1, $y1+15 );
	
	
	$r1  = $this->w - 70;
    $r2  = $r1 + 60;
    $y1  = $this->h - 40+13;
    $y2  = $y1+7;
	
	$this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1),"DF");
	$this->SetFont( "Arial", "B", 9);
    $this->Cell(20,4, "TOTAL", 0, 0, "L");
}

// remplit les cadres TVA / Totaux et la remarque
// params  = array( "RemiseGlobale" => [0|1],
//                      "remise_tva"     => [1|2...],  // {la remise s'applique sur ce code TVA}
//                      "remise"         => value,     // {montant de la remise}
//                      "remise_percent" => percent,   // {pourcentage de remise sur ce montant de TVA}
//                  "FraisPort"     => [0|1],
//                      "portTTC"        => value,     // montant des frais de ports TTC
//                                                     // par defaut la TVA = 19.6 %
//                      "portHT"         => value,     // montant des frais de ports HT
//                      "portTVA"        => tva_value, // valeur de la TVA a appliquer sur le montant HT
//                  "AccompteExige" => [0|1],
//                      "accompte"         => value    // montant de l'acompte (TTC)
//                      "accompte_percent" => percent  // pourcentage d'acompte (TTC)
//                  "Remarque" => "texte"              // texte
// tab_tva = array( "1"       => 19.6,
//                  "2"       => 5.5, ... );
// invoice = array( "px_unit" => value,
//                  "qte"     => qte,
//                  "tva"     => code_tva );
function addTVAs($invoice)
{
	
	$accompteTTC=0;
    $this->SetFont('Arial','',8);
    
    reset ($invoice);
    $px = array();
    while ( list( $k, $prod) = each( $invoice ) )
    {
		
        @ $px[1] += $prod["qte"] * $prod["px_unit"];
		
		if ($prod["taxable"]=="1"){
			
			$accompteTTC+= round(($prod["qte"] * $prod["px_unit"])*($prod["rate"]/100),2,PHP_ROUND_HALF_UP);
			
		}
		
    }

    $prix     = array();
    $totalHT  = 0;
    $totalTTC = 0;
    $totalTVA = 0;
    $y = 261;
    reset ($px);
    natsort( $px );
    while ( list($code_tva, $articleHT) = each( $px ) )
    {
        $totalTTC += $articleHT;
}

//tax
 $accompteTTC=sprintf("%.2F", $accompteTTC);

    $re  = $this->w - 50;
    $rf  = $this->w - 29;
    $y1  = $this->h - 40;
    $this->SetFont( "Arial", "", 8);
	$this->SetTextColor(100,100,100);
    $this->SetXY( $rf, $y1+4 );
    $this->Cell( 17,4, sprintf("%0.2F", $totalTTC), '', '', 'R');
   $this->SetXY( $rf, $y1+8 );
    $this->Cell( 17,4, sprintf("%0.2F", $accompteTTC), '', '', 'R');
    $this->SetXY( $rf, $y1+15 );
	$this->SetFont( "Arial", "B", 10);
	$this->SetTextColor(0,0,0);
    $this->Cell( 17,4, sprintf("%0.2F", $totalTTC + $accompteTTC), '', '', 'R');
    
}


function stamp( $texte )
{
    $this->SetFont('Arial','B',50);
    $this->SetTextColor(203,203,203);
    $this->Rotate(45,55,190);
    $this->Text(100,190,$texte);
    $this->Rotate(0);
    $this->SetTextColor(0,0,0);
}

}
?>