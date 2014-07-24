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
namespace KMX\Persistence;

use KM\Lang\Enum;

/**
 * *lush mode setting.
 *
 * <p> When queries are executed within a transaction, if <code>FlushModeType.AUTO</code> is set on
 * the <code>javax.persistence.Query</code> or <code>javax.persistence.TypedQuery</code> object, or
 * if the flush mode setting for the persistence context is <code>AUTO</code> (the default) and a
 * flush mode setting has not been specified for the <code>Query</code> or <code>TypedQuery</code>
 * object, the persistence provider is responsible for ensuring that all updates to the state of all
 * entities in the persistence context which could potentially affect the result of the query are
 * visible to the processing of the query. The persistence provider implementation may achieve this
 * by flushing those entities to the database or by some other means.
 *
 * <p> If <code>FlushModeType.COMMIT</code> is set, the effect of updates made to entities in the
 * persistence context upon queries is unspecified.
 *
 * <p> If there is no transaction active or the persistence context is not joined to the current
 * transaction, the persistence provider must not flush to the database.FlushModeType Class
 *
 * @package KMX\Persistence
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class FlushModeType extends Enum {
	
	/**
	 * Flushing to occur at transaction commit.
	 * The provider may flush at other times, but is not required to.
	 */
	const COMMIT = 'commit';
	
	/**
	 * (Default) Flushing to occur at query execution.
	 */
	const AUTO = 'auto';
}
?>