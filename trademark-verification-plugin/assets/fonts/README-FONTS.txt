Font Files for Certificate Generation
======================================

This directory should contain the DejaVuSans.ttf font file for certificate
text rendering using PHP GD imagettftext().

Download DejaVuSans.ttf from:
https://github.com/dejavu-fonts/dejavu-fonts/releases

Place the file here as:
  assets/fonts/DejaVuSans.ttf

If the font file is not present, the certificate generator will fall back to
GD built-in bitmap fonts (imagestring). The result will be less polished but
still functional.

License: DejaVu fonts are released under a free license (see dejavu-fonts
project for details).
