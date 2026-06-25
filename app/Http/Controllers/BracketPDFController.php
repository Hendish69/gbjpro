<?php

namespace App\Http\Controllers;
use App\Models\Tournament;
use Carbon\Carbon;

use TCPDF;

class BracketPDFController extends Controller
{
    public function generate(Tournament $tournament)
    {
     
      
        $tournament->load([
            'matches.player1', 
            'matches.player2',
            'players.ptmClub',
            'tournamentPlayers.player.ptmClub',
            'tournamentPlayers.representingClub',
            'duoPairs.player1.ptmClub', 
            'duoPairs.player2.ptmClub'
        ]);
        $title = $tournament->name;
       
        $day = Carbon::parse($tournament->start_date, 'Asia/Jakarta');
        Carbon::setLocale('id');
        $hari = $day->translatedFormat('l');
        $date =$hari.', '. $day->format('d F Y');

     
        // dd($tournament->duoPairs,$tournament,$date,$title);
        // === Setup PDF ===
        $pdf = new TCPDF('L', 'mm', 'A3', true, 'UTF-8', false);
        $pdf->SetCreator('Hendi');
        $pdf->SetAuthor('GBJPro');
        $pdf->SetTitle($title);
        $pdf->SetMargins(10, 10, 10);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true,0);
        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        // dd($lbrPage/2);
        // === Header ===
        $symb='images/GbjPro.png';
        $pdf->Image($symb,130,3,30);
        $pdf->SetFont('helvetica', 'B', 30);
        $pdf->Cell(0, 10, 'PTM GBJ PRO', 0, 1, 'C');
        $x = $pdf->GetX();
        $y = $pdf->GetY();
       
        $pdf->SetXY($pdf->GetX()+3, $y);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Rect($x, $y, 25,10,'FD');
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell($pageWidth / 3, 10, 'MEJA 1', 0, 0, 'L');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'B', 22);
        $pdf->Cell($pageWidth / 3-7, 10, $title, 0, 0, 'C');
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Rect($pageWidth-17, $y, 25,10,'FD');
        $pdf->SetXY($pdf->GetX()-2, $y);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell($pageWidth / 3, 10, 'MEJA 2', 0, 1, 'R');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 14);
        $pdf->Cell(0, 10, $date, 0, 0, 'C');
       
        $pdf->Ln(10);

        // === Setup posisi dasar ===
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.4);
        $pdf->SetFont('helvetica', '', 9);
        $this->bagan24pasang( $pdf,$tournament);
        // $xStart = 20;        // awal X
        // $yStart = 45;        // awal Y
        // $matchHeight = 12;   // tinggi 1 kotak nama
        // $gapY = 8;           // jarak antar match
        // $roundGap = 45;      // jarak antar ronde (X)
        // $boxWidth = 30;      // lebar kotak nama
        // $scoreWidth = 8;     // lebar kolom skor
        // $numPlayers = count($tournament->duoPairs); // jumlah peserta
        // $bye=$this->indexbagan($numPlayers);
        // $jam=date('H:i', strtotime($tournament->daily_start_time));
        // $pairs=collect($tournament->duoPairs)->sortBy('id')->values();
        // //  dd($pairs[0],$tournament->duoPairs[0]);
        // $divbagan= $numPlayers/2;
        
        // // dd($pairs, $numPlayers, $divbagan);
        // for ($i = 1; $i <=  $divbagan; $i++) {
          
        //     // dd($pairs[0]->player1->nickname,$pairs);
        //     $circleX = $xStart - 7;
        //     $krng=10;
        //     if ( in_array($i,$bye) ) {
        //         $circleX=$circleX+40;
        //         $krng=50;
        //     }
        //     $pjggrs=55;$jrktext=4;
        //     $circleY = $yStart + (($i) * ($matchHeight + $gapY));
        //     $pos=$this->drawNumberCircle($pdf, $circleX, $circleY + ($matchHeight / 2), $i);
        //     $pdf->Line($circleX+$jrktext, $circleY+6 , $circleX+ $pjggrs, $circleY+6 );
        //     // dd( $pos);
        //     $plyr1=$this->shortName($pairs[$i-1]->player1->nickname);
        //     $plyr2=$this->shortName($pairs[$i-1]->player2->nickname);
        //     $player=$plyr1.' / '.$plyr2;
        //     $pdf->setXY($circleX+5,$circleY);
        //     $pdf->cell(0,$jrktext, $player, 0, 0, 'L');
           
        //     $posx=$circleX+3;
        //     // dd($pos,$pos[0],$pos[0]+$circleX,$pdf->getX());
        //     $this->drawLine($pdf, $posx, $circleY+6 ,60,$player,'L',$i,$jam);
        //     $circleX = $xStart- $krng + $pageWidth ;
        //     $circleY = $yStart + (($i) * ($matchHeight + $gapY));
        //     $pos= $this->drawNumberCircle($pdf, $circleX, $circleY + ($matchHeight / 2), $i+12);
        //     $pdf->Line($circleX- ($pjggrs+$jrktext), $circleY+6 , $circleX-$jrktext, $circleY+6 );
        //     $plyr1=$this->shortName($pairs[$i+$divbagan-1]->player1->nickname);
        //     $plyr2=$this->shortName($pairs[$i+$divbagan-1]->player2->nickname);
        //     $player=$plyr1.' / '.$plyr2;
        //     $pdf->setXY($circleX-($pjggrs+$jrktext),$circleY);
        //     $pdf->cell($pjggrs,$jrktext, $player, 0, 0, 'R');
        //     $posx=$circleX-63;
        //     $this->drawLine($pdf, $posx,  $circleY+6 ,60,$player,'R',$i,$jam);
            
        //     $jam=date('H:i', strtotime($jam . ' +45 minutes'));
        // }
       
       

        // // === Tambah label ronde ===
        // $roundTitles = ['Preliminary', 'Round 2', 'Quarterfinal', 'Final'];
        // $x = $xStart;
        // $pdf->SetFont('helvetica', 'B', 10);
        // foreach ($roundTitles as $rTitle) {
        //     $pdf->Text($x + 5, $yStart - 10, $rTitle);
        //     $x += $roundGap;
        // }

        // === Watermark ===
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->Text(15, 285, '© GBJ PRO TUMINI INTERN DUO 2025');

        // === Output PDF ===
        $pdf->Output('TUMINI_INTERN_DUO.pdf', 'I');
    }

    function bagan24pasang( $pdf,$tournament){
        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $xStart = 20;        // awal X
        $yStart = 30;        // awal Y
        $matchHeight = 12;   // tinggi 1 kotak nama
        $gapY = 8;           // jarak antar match
        $roundGap = 45;      // jarak antar ronde (X)
        $boxWidth = 30;      // lebar kotak nama
        $scoreWidth = 8;     // lebar kolom skor
        $numPlayers = count($tournament->duoPairs); // jumlah peserta
        $bye=$this->indexbagan($numPlayers);
        $jam=date('H:i', strtotime($tournament->daily_start_time));
        $pairs=collect($tournament->duoPairs)->sortBy('id')->values();
        //  dd($pairs[0],$tournament->duoPairs[0]);
        $divbagan= $numPlayers/2;
        
        // dd($pairs, $numPlayers, $divbagan);
        for ($i = 1; $i <=  $divbagan; $i++) {
          
            // dd($pairs[0]->player1->nickname,$pairs);
            $circleX = $xStart - 7;
            $krng=10;
            if ( in_array($i,$bye) ) {
                $circleX=$circleX+40;
                $krng=50;
            }
            $pjggrs=55;$jrktext=4;
            $circleY = $yStart + (($i) * ($matchHeight + $gapY));
            $pos=$this->drawNumberCircle($pdf, $circleX, $circleY + ($matchHeight / 2), $i);
            $pdf->Line($circleX+$jrktext, $circleY+6 , $circleX+ $pjggrs, $circleY+6 );
            // dd( $pos);
            $plyr1=$this->shortName($pairs[$i-1]->player1->nickname);
            $plyr2=$this->shortName($pairs[$i-1]->player2->nickname);
            $player=$plyr1.' / '.$plyr2;
            $pdf->setXY($circleX+5,$circleY);
            $pdf->cell(0,$jrktext, $player, 0, 0, 'L');
           
            $posx=$circleX+3;
            // dd($pos,$pos[0],$pos[0]+$circleX,$pdf->getX());
            $this->drawLine($pdf, $posx, $circleY+6 ,60,$player,'L',$i,$jam);
            $circleX = $xStart- $krng + $pageWidth ;
            $circleY = $yStart + (($i) * ($matchHeight + $gapY));
            $pos= $this->drawNumberCircle($pdf, $circleX, $circleY + ($matchHeight / 2), $i+12);
            $pdf->Line($circleX- ($pjggrs+$jrktext), $circleY+6 , $circleX-$jrktext, $circleY+6 );
            $plyr1=$this->shortName($pairs[$i+$divbagan-1]->player1->nickname);
            $plyr2=$this->shortName($pairs[$i+$divbagan-1]->player2->nickname);
            $player=$plyr1.' / '.$plyr2;
            $pdf->setXY($circleX-($pjggrs+$jrktext),$circleY);
            $pdf->cell($pjggrs,$jrktext, $player, 0, 0, 'R');
            $posx=$circleX-63;
            $this->drawLine($pdf, $posx,  $circleY+6 ,60,$player,'R',$i,$jam);
            
            $jam=date('H:i', strtotime($jam . ' +45 minutes'));
        }
       
       

        // === Tambah label ronde ===
        // $roundTitles = ['Preliminary', 'Round 2', 'Quarterfinal', 'Final'];
        // $x = $xStart;
        // $pdf->SetFont('helvetica', 'B', 10);
        // foreach ($roundTitles as $rTitle) {
        //     $pdf->Text($x + 5, $yStart - 10, $rTitle);
        //     $x += $roundGap;
        // }
    }

    function indexbagan($jmlpeserta){
        switch ($jmlpeserta) {
            case '24':
                return [1,6,7,12,13,18,19,24];
                break;
            case '23':
                return [1,6,7,12,13,18,19,24];
                break;
            case '22':
                return [3,8,11,14,17,22];
                break;
            case '21':
                return [1,6,7,12,13,18,19,24];
                break;
        }
    }
    function drawNumberCircle($pdf, $x, $y, $number, $radius = 3) {
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Circle($x, $y, $radius, 0, 360, 'FD');

        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 14);

        // Hitung offset teks agar lebih tengah proporsional
        $offsetX = ($radius / 2)+0.5;
        $offsetY = ($radius / 2)+1.5 ;
        if ($number >= 10) {
            $offsetX += 1.7; // tambahan offset untuk dua digit
        }
        if ($number == 1) {
            $offsetX += 0.5; // tambahan offset untuk dua digit
        }
        $pdf->Text($x - $offsetX, $y - $offsetY, strval($number));

        $pdf->SetTextColor(0, 0, 0);
        return [$pdf->getX(),$pdf->getY()];
    }
    function drawLine($pdf, $x, $y, $length,$name='TEST',$align='L',$idx,$jam='08:00') {
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetDrawColor(0, 0, 0);
        //garis horizontal untuk nama
        $pdf->Line($x, $y, $x+$length, $y);
      
        $pdf->SetFont('helvetica', 'B', 14);
        $posx_v=round($x);
        $tx1=40;
        $tx2=35;
        $tx3=20;
        $postextX=10;
        if ($align=='R') {
            // $pdf->text($x+$length - ($pdf->GetStringWidth($name))-6 , $y-7 , $name);
            $tx1=-40;
            $tx2=-35;
            $tx3=-20;
            $postextX=-25;
        }else{
            // $pdf->text($x+2 , $y-7 , $name);
            $posx_v=$x+$length;
        }
        $vertline=true;
        $ty1=10;
        if (in_array($idx,[3,5,9,11,15,17,21,23]) ) {
            $vertline=false;
        }
        $tmbhy=20;
        $dlpnbsr=false;
        if ( in_array($idx,[1,7,13,19]) ) {
            $tmbhy=30;
            $ty1=15;
            $dlpnbsr=true;
        }
        if ( in_array($idx,[6,12,18,24]) ) {
            $tmbhy=-30;
            $ty1=-15;
        }
        if ($vertline){
            // garis vertikal 
            $pdf->Line($posx_v, $y, $posx_v, $y+$tmbhy);
           
            if ( in_array($idx,[1,6,7,12]) ) {
                $tx1-=5;
                 if ($align=='R') {
                    $tx1+=10;
                 }
            }
            // garis horizontal penghubung, babak selanjutnya
            $pdf->Line($posx_v, $y+$ty1, $posx_v+$tx1, $y+$ty1);
            $pdf->Rect($posx_v+$postextX, $y+$ty1, 15,6,'FD');
            $pdf->SetFillColor(0, 0, 0);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->text($posx_v+$postextX, $y+$ty1, $jam);
            $pdf->SetTextColor(0, 0, 0);
        }
        $finalx=0;
        $finaly= 10;
        if ($dlpnbsr){
            $tggi=85;
            $tggi2=85/2;
            $pdf->Line($posx_v+$tx2, $y+$ty1, $posx_v+$tx2, $y+$tggi );
            $pdf->Line($posx_v+$tx2, $y+$ty1+$tggi2-7, $posx_v+$tx2+$tx2, $y+$ty1+$tggi2-7 );
            if ( in_array($idx,[1,13]) ) {
                $pdf->Line( $posx_v+$tx2+$tx2, $y+$ty1+$tggi2-7,  $posx_v+$tx2+$tx2, $y+$ty1+$tggi2-7+120 );
                $pdf->text($posx_v+$tx2+$tx2, $y+$ty1+$tggi2-7+60 , 'FINAL');
                $pdf->Line( $posx_v+$tx2+$tx2, $y+$ty1+$tggi2-7+60,  $posx_v+$tx2+$tx2 + $tx3, $y+$ty1+$tggi2-7+60 );
                $finalx= $posx_v+$tx2+$tx2 + $tx3;
                $finaly= $y+$ty1+$tggi2-7+60;
               
            }
            if ( $idx==1 ) {
                // $pdf->Line( $posx_v+$tx2+$tx2, $y+$ty1+$tggi2-7,  $posx_v+$tx2+$tx2, $y+$ty1+$tggi2-7+120 );
                // $pdf->Line( $posx_v+$tx2+$tx2, $y+$ty1+$tggi2-7+60,  $posx_v+$tx2+$tx2 + $tx3, $y+$ty1+$tggi2-7+60 );
                $finalx= $posx_v+$tx2+$tx2 + $tx3;
                $finaly= $y+$ty1+$tggi2-7+60;
                $pdf->text($finalx, $finaly , 'FINAL');
               
            }
        }
        $pdf->SetTextColor(0, 0, 0);
       
    }

    function shortName($fullName) {
        $parts = explode(' ', trim($fullName));

        if (count($parts) > 1) {
            if ( substr($parts[1], 0, 1) == '(' ) {
                return ucwords(strtolower($fullName));
            }elseif (in_array(strtoupper($parts[0]),['H.','COACH','USTD.','DHE'])){
                return ucwords(strtolower($fullName));
            }elseif (strlen($parts[1])<4){
                return ucwords(strtolower($fullName));
            }
            return ucwords(strtolower($parts[0]) . ' ' . substr($parts[1], 0, 1) . '.');
        }

        return ucwords(strtolower($fullName));
    }
}
