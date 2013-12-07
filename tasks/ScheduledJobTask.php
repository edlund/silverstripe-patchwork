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

class ScheduledJobTask extends BuildTask {
	
	private static $allowed_ips = array(
		'127.0.0.1',
		'127.0.1.1',
		'::1'
	);
	
	protected $title = 'Run scheduled jobs';
	protected $description = 'Set up a cron tab entry to run this task every hour.';
	
	public function run($request) {
		if (!(php_sapi_name() === 'cli' || in_array($_SERVER['REMOTE_ADDR'],
				$this->config()->allowed_ips))) {
			return $this->httpError(403);
		}
		if (Member::currentUserID()) {
			Member::currentUser()->logOut();
		}
		
		$now = date('Y-m-d H:i:s');
		$jobs = ScheduledJob::get()->where("\"Scheduled\" < '$now'")
			->where('"Completed" IS NULL')
			->where('"Failed" IS NULL');
		
		foreach ($jobs as $job) {
			$job->run();
		}
	}
	
}