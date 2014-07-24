<?php

/**
 * Kaaterskil Library
 *
 * PHP version 5.5
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY MERCHANTABILITY AND
 * FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL KAATERSKIL MANAGEMENT, LLC BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
 * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category    Kaaterskil
 * @copyright   Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version     SVN $Id$
 */
namespace Sun\Misc\Launcher;

use KM\Net\URLClassLoader;

/**
 * AppClassLoader Class
 *
 * @package Sun\Misc
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class AppClassLoader extends URLClassLoader {
	private $urls;
	private $path;

	public static function getAppClassLoader() {
		$s = dirname( dirname( dirname( dirname( __FILE__ ) ) ) );
		$urls[0] = $s;
		return new self( $urls, null );
	}

	public function __construct(array $urls, ClassLoader $parent = null) {
		parent::__construct();
		$this->urls = $urls;
		$this->path = $urls;
	}

	public function getUrls() {
		$returnValue = [];
		foreach ( $this->urls as $url ) {
			$returnValue[] = $url;
		}
		return $returnValue;
	}
}
?>