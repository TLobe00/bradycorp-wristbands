<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class testGDImg extends Controller
{
    //
    public function handle(Request $request) {
//      print 'Hi';
//header("Content-Type: image/png");
//$im = @imagecreate(510, 120)
//    or die("Cannot Initialize new GD image stream");
//$background_color = imagecolorallocate($im, 0, 0, 0);
//$text_color = imagecolorallocate($im, 233, 14, 91);
//imagestring($im, 3, 20, 20,  "A Simple Text String", $text_color);
//print imagepng($im);
//imagedestroy($im);

$hexcode = $request->input('hex');
//$imagepath = $request->input('img');
$imageupl = $request->file('imgupl')->store('images');
$bgcolor = $request->input('bgcolor');

dd($imageupl);

//$file = storage_path( 'images/'.$imagepath );
$size = getimagesize($file);


$imageTypeArray = array
    (
        0=>'UNKNOWN',
        1=>'GIF',
        2=>'JPEG',
        3=>'PNG',
        4=>'SWF',
        5=>'PSD',
        6=>'BMP',
        7=>'TIFF_II',
        8=>'TIFF_MM',
        9=>'JPC',
        10=>'JP2',
        11=>'JPX',
        12=>'JB2',
        13=>'SWC',
        14=>'IFF',
        15=>'WBMP',
        16=>'XBM',
        17=>'ICO',
        18=>'COUNT'
    );

    $size[2] = $imageTypeArray[$size[2]];

    switch ($size[2]) {
        case 'JPEG':
            $picture = imagecreatefromjpeg($file);
            break;
        case 'GIF':
            $picture = imagecreatefromgif($file);
            break;
        case 'PNG':
            $picture = imagecreatefrompng($file);
            break;
        case 'BMP':
            $picture = imagecreatefrombmp($file);
            break;
    }

    //dd($size[2]);

//dd($size);
/*
$im = imagecreatefromjpeg($file);
$white = imagecolorallocate($im, 255, 255, 255);
imagecolortransparent($im, $white);
imagecolorallocatealpha( $im, 255, 255, 255, 127 );
imagealphablending($im, true);
imagesavealpha($im, true);
imagecolorallocatealpha($im, 255, 255, 255, 0);
*/
//$picture = imagecreatefromjpeg($file);

$img_w = imagesx($picture);
$img_h = imagesy($picture);

$newPicture = imagecreatetruecolor( $img_w, $img_h );
imagesavealpha( $newPicture, true );
imagealphablending( $newPicture, true );
$rgb = imagecolorallocatealpha( $newPicture, 255, 255, 255, 127 );
imagefill( $newPicture, 0, 0, $rgb );

$color = imagecolorat( $picture, $img_w-1, 1);

for( $x = 0; $x < $img_w; $x++ ) {
    for( $y = 0; $y < $img_h; $y++ ) {
        $c = imagecolorat( $picture, $x, $y );

        $colorInfo = imagecolorsforindex( $picture, $c );
        #$r = ($c >> 16) & 0xFF;
        #$g = ($c >> 8) & 0xFF;
        #$b = $c & 0xFF;

        $r = hexdec(substr($hexcode,0,2));
        $g = hexdec(substr($hexcode,2,2));
        $b = hexdec(substr($hexcode,4,2));

        //dd(substr($hexcode,0,2) . ', ' . substr($hexcode,2,2) . ', ' . substr($hexcode,4,2));

        $whitearry = array(
          'red' => 255,
          'green' => 255,
          'blue' => 255,
          'alpha' => 0
        );
        $customarry = array(
          'red' => $r,
          'green' => $g,
          'blue' => $b,
          'alpha' => 0
        );
        //$distance = pow(($colorInfo['red']), 2) + pow(($colorInfo['green']), 2) + pow(($colorInfo['blue']), 2) + pow(($colorInfo['alpha']), 2);
        $distance = $this->colorsAreClose( $whitearry, $colorInfo, 1000 );
        if (isset($distance2)) {
          $distance2 = $this->colorsAreClose( $customarry, $colorInfo, 1000 );
        } else {
          $distance2 = false;
        }

        if( $color != $c ){
            if ($distance) {
              imagesetpixel( $newPicture, $x, $y, $rgb );
            } elseif ($distance2) {
              imagesetpixel( $newPicture, $x, $y, $rgb );
            } else {
              imagesetpixel( $newPicture, $x, $y, $c );
            }
        }


    }
}
ob_start();
imagepng($newPicture);
imagedestroy($newPicture);
imagedestroy($picture);


//ob_start();
//imagepng($im);
//imagedestroy($im);
$i = ob_get_clean();

echo '<body bgcolor="#'.$bgcolor.'">';
echo "<img src='data:image/png;base64," . base64_encode( $i )."'>"; //saviour line!
echo '</body>';

    }

    /**
    * Function to compare two colors by RGBa, within a tolerance.
    *
    * Very basic, just compares the sum of the squared differences for each of
    * the R, G, B, a components of two colors against a 'tolerance' value.
    *
    * @param int[] $color_a
    *   An RGBa array.
    * @param int[] $color_b
    *   An RGBa array.
    * @param int $tolerance
    *   The accepteable difference between the colors.
    *
    * @return bool
    *   TRUE if the colors differences are within tolerance, FALSE otherwise.
    */
   protected function colorsAreClose(array $color_a, array $color_b, $tolerance) {
     // Fully transparent colors are equal, regardless of RGB.
     if ($color_a['alpha'] == 127 && $color_b['alpha'] == 127) {
       return TRUE;
     }
     $distance = pow(($color_a['red'] - $color_b['red']), 2) + pow(($color_a['green'] - $color_b['green']), 2) + pow(($color_a['blue'] - $color_b['blue']), 2) + pow(($color_a['alpha'] - $color_b['alpha']), 2);
     //dd($distance);
     if ($distance > $tolerance) {
       //debug("Color A: {" . implode(',', $color_a) . "}, Color B: {" . implode(',', $color_b) . "}, Distance: " . $distance . ", Tolerance: " . $tolerance);
       return FALSE;
     }
     return TRUE;
   }

}
