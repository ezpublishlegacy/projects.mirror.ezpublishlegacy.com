<?php
//
// Created on: <03-Jun-2004 13:30:57 kk>
//
// Copyright (C) 1999-2004 eZ systems as. All rights reserved.
//
// This source file is part of the eZ publish (tm) Open Source Content
// Management System.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 as published by the Free
// Software Foundation and appearing in the file LICENSE.GPL included in
// the packaging of this file.
//
// Licencees holding valid "eZ publish professional licences" may use this
// file in accordance with the "eZ publish professional licence" Agreement
// provided with the Software.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The "eZ publish professional licence" is available at
// http://ez.no/products/licences/professional/. For pricing of this licence
// please contact us via e-mail to licence@ez.no. Further contact
// information is available at http://ez.no/home/contact/.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//

if ( count( $argv ) != 2 )
{
    echo
        "Usage   : php afm2font.php <fontname>\n" .
        "Example : php afm2font.php Times-Roman\n" .
        "Output  : php_Times-Roman.font\n\n";
    exit(0);
}

$data = array();
echo "Processing : " . $argv[1] . ".afm ... \n";
$file = file($argv[1].'.afm');
foreach ($file as $rowA)
{
    $row=trim($rowA);
    $pos=strpos($row,' ');
    if ($pos){
        // then there must be some keyword
        $key = substr($row,0,$pos);
        switch ($key){
            case 'FontName':
            case 'FullName':
            case 'FamilyName':
            case 'Weight':
            case 'ItalicAngle':
            case 'IsFixedPitch':
            case 'CharacterSet':
            case 'UnderlinePosition':
            case 'UnderlineThickness':
            case 'Version':
            case 'EncodingScheme':
            case 'CapHeight':
            case 'XHeight':
            case 'Ascender':
            case 'Descender':
            case 'StdHW':
            case 'StdVW':
            case 'StartCharMetrics':
                $data[$key]=trim(substr($row,$pos));
            break;
            case 'FontBBox':
                $data[$key]=explode(' ',trim(substr($row,$pos)));
            break;
            case 'C':
                //C 39 ; WX 222 ; N quoteright ; B 53 463 157 718 ;
                $bits=explode(';',trim($row));
            $dtmp=array();
            foreach($bits as $bit){
                $bits2 = explode(' ',trim($bit));
                if (strlen($bits2[0])){
                    if (count($bits2)>2){
                        $dtmp[$bits2[0]]=array();
                        for ($i=1;$i<count($bits2);$i++){
                            $dtmp[$bits2[0]][]=$bits2[$i];
                        }
                    } else if (count($bits2)==2){
                        $dtmp[$bits2[0]]=$bits2[1];
                    }
                }
            }
            if ($dtmp['C']>=0){
                $data['C'][$dtmp['C']]=$dtmp;
                $data['C'][$dtmp['N']]=$dtmp;
            } else {
                $data['C'][$dtmp['N']]=$dtmp;
            }
            break;
            case 'KPX':
                //KPX Adieresis yacute -40
                $bits=explode(' ',trim($row));
            $data['KPX'][$bits[1]][$bits[2]]=$bits[3];
            break;
        }
    }
}

$data['_version_']=1;
$fp = fopen('php_'.$argv[1].'.font','w');
fwrite($fp,serialize($data));
fclose($fp);
echo 'Wrote : php_'.$argv[1].'.font'."\n";

?>
