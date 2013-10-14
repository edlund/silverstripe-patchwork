<?php

/**
 * Copyright (c) 2013, Redema AB - http://redema.se/
 * 
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 * 
 * * Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * 
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 * 
 * * Neither the name of Redema, nor the names of its contributors may be used
 *   to endorse or promote products derived from this software without specific
 *   prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

class PatchworkRequirements extends Extension {
	
	/**
	 * @TODO: Add support for minifications for live environments
	 * and clean up the code.
	 */
	public function onBeforeInit() {
		Requirements::css('patchwork/css/thirdparty/bootstrap-3.0.0.css');
		Requirements::css('patchwork/css/thirdparty/bootstrap-theme-3.0.0.css');
		Requirements::css('patchwork/css/FrontEndForm.css');
		
		Requirements::css('mysite/css/layout.css');
		Requirements::css('mysite/css/typography.css');
		Requirements::css('mysite/css/form.css');
		
		Requirements::javascript('patchwork/javascript/thirdparty/modernizr-2.6.2.js');
		Requirements::javascript('patchwork/javascript/thirdparty/respond-72cd8e1437769cbbf1fda171c334e71141785563.js');
		Requirements::javascript('patchwork/javascript/thirdparty/picturefill-431555909a4dce9faee6e3364fbade7dd0e92128.js');
		Requirements::javascript('patchwork/javascript/thirdparty/jquery-1.10.2.js');
		Requirements::javascript('patchwork/javascript/thirdparty/jquery-migrate-1.1.1.js');
		Requirements::javascript('patchwork/javascript/thirdparty/jquery-mobile-1.3.1.custom.js');
		Requirements::javascript('patchwork/javascript/thirdparty/jquery-refineslide-0.4.1.js');
		Requirements::javascript('patchwork/javascript/thirdparty/bootstrap-3.0.0.js');
		
		Requirements::javascript('patchwork/javascript/FrontEndForm.js');
		Requirements::javascript('patchwork/javascript/Patchwork.js');
		
		Requirements::javascript('mysite/javascript/mysite.js');
	}
	
}
