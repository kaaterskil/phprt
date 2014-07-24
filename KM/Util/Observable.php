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
namespace KM\Util;

/**
 * This interface represents an observable object, or "data" in the model-view paradigm.
 * <p>
 * An observable object can have one or more observers. An observer may be any object that
 * implements interface <tt>Observer</tt>. After an observable instance changes, an application
 * calling the <code>Observable</code>'s <code>notifyObservers</code> method causes all of its
 * observers to be notified of the change by a call to their <code>update</code> method.
 * <p>
 * The order in which notifications will be delivered is unspecified. The default implementation
 * provided in the Observable interface will notify Observers in the order in which they registered
 * interest, but subclasses may change this order, use no guaranteed order, deliver notifications on
 * separate threads, or may guarantee that their subclass follows this order, as they choose.
 * <p>
 * When an observable object is newly created, its set of observers is empty. Two observers are
 * considered the same if and only if the <tt>equals</tt> method returns true for them.
 *
 * @package KM\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
interface Observable {

	/**
	 * Adds an observer to the set of observers for this object, provided that it is not the same as
	 * some observer already in the set.
	 * The order in which notifications will be delivered to multiple observers is not specified.
	 * @param Observer $o An observer to be added.
	 */
	public function addObserver(Observer $o);

	/**
	 * Deletes an observer from the set of observers of this object.
	 * Passing <code>null</code> to this method will have no effect.
	 * @param Observer $o The observer to be deleted.
	 */
	public function deleteObserver(Observer $o);

	/**
	 * If this object has changed, as indicated by the <code>hasChanged</code> method, than notify
	 * all of its observers and then call the <code>clearChanged</code> method to indicate that this
	 * object has no longer changed.
	 *
	 * <p>Each observer has its own <code>update</code> method called with two arguments: this
	 * observable object and the <code>arg</code> argument.
	 * @param mixed $arg
	 */
	public function notifyObservers($arg = null);

	/**
	 * Clears the observer list so that this object no longer has any observers.
	 */
	public function deleteObservers();

	/**
	 * Tests if this object has changed.
	 * @return boolean <code>True</code> if and only if the <code>setChanged</code> method has been
	 *         called more recently than the <code>clearChanged</code> method on this object;
	 *         <code>false</code> otherwise.
	 */
	public function hasChanged();

	/**
	 * Returns the number of observers of this <code>Observable</code> object.
	 * @return int The number of observers of this object.
	 */
	public function countObservers();
}
?>