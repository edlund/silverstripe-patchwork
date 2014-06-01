<?php

/**
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <gohr@cosmocode.de>
 * Based on DokuWiki's Anti Spam module: https://www.dokuwiki.org/antispam
 * 
 * Copyright (c) 2014, Redema AB - http://redema.se/
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

/**
 * Listen to CaptchaWord challenges. Make sure that there are
 * appropriate wav files to form the word in question.
 */
class CaptchaAudioController extends Controller {
	
	private static $wav_paths = array(
		'patchwork/assets/captcha'
	);
	
	private static $allowed_actions = array(
		'listen'
	);
	
	/**
	 * Join multiple wav files.
	 * 
	 * All wave files need to have the same format and must
	 * be uncompressed. The headers of the last file will be
	 * used (with recalculated datasize of course).
	 * 
	 * @link http://ccrma.stanford.edu/CCRMA/Courses/422/projects/WaveFormat/
	 * @link http://www.thescripts.com/forum/thread3770.html
	 */
	public static function join_wavs(array $wavs) {
		$data = '';
		$fields = join('/', array(
			'H8ChunkID',
			'VChunkSize',
			'H8Format',
			'H8Subchunk1ID',
			'VSubchunk1Size',
			'vAudioFormat',
			'vNumChannels',
			'VSampleRate',
			'VByteRate',
			'vBlockAlign',
			'vBitsPerSample'
		));
		
		foreach($wavs as $wav) {
			$file = fopen($wav, 'rb');
			$header = fread($file, 36);
			$info = unpack($fields, $header);
			
			// Read optional extra stuff.
			if ($info['Subchunk1Size'] > 16) {
				$header .= fread($file, $info['Subchunk1Size'] - 16);
			}
			
			// Read SubChunk2ID.
			$header .= fread($file, 4);

			// Read Subchunk2Size.
			$size = unpack('vsize', fread($file, 4));
			$size = $size['size'];

			// Read data.
			$data .= fread($file, $size);
		}
		
		return $header . pack('V', strlen($data)) . $data;
	}
	
	public function listen() {
		$class = $this->request->getVar('Type');
		$classes = ClassInfo::subclassesFor('CaptchaWord');
		array_shift($classes);
		if (!in_array($class, $classes))
			return $this->httpError(404);
		
		$key = Captcha::get_session_key('Word', $class);
		$word = Session::get($key);
		$chars = Stringy::str_split($word);
		$paths = array();
		
		foreach ($chars as $char) {
			foreach ($this->config()->wav_paths as $path) {
				$path = BASE_PATH . "/{$path}/{$char}.wav";
				if (file_exists($path)) {
					$paths[] = $found = $path;
					break;
				}
			}
			if (!isset($found))
				return $this->httpError(500);
			else
				unset($found);
		}
		
		$this->response->addHeader('Content-Type', 'audio/x-wav');
		$this->response->addHeader('Content-Disposition', 'attachment; filename=captcha.wav');
		return $this->join_wavs($paths);
	}
	
}

