Yii 2 File DB extension Change Log
==================================

1.0.6, April 9, 2018
--------------------

- Bug #10: Fixed `ActiveQuery::indexBy()` does not apply while using Yii 2.0.14 (klimov-paul)


1.0.5, November 3, 2017
-----------------------

- Bug: Fixed `count()` usage at `QueryProcessor::filterInCondition()` for compatibility with PHP 7.2 (klimov-paul)


1.0.4, July 7, 2017
-------------------

- Bug #8: Fixed `QueryProcessor` is unable to process comparison condition, e.g. `column operator value` (klimov-paul)


1.0.3, February 6, 2017
-----------------------

- Enh: `QueryProcessor::filterHashCondition()` allows to specify filter value as `\Closure` instance (klimov-paul)
- Enh #5: `QueryProcessor::filterInCondition()` advanced allowing comparison against array-type columns (klimov-paul)
- Enh #6: Added `QueryProcessor::filterCallbackCondition()` allowing to specify PHP callback as filter (klimov-paul)


1.0.2, June 28, 2016
--------------------

- Bug #4: Fixed `Query` unable to fetch default connection component for data fetching (klimov-paul)


1.0.1, June 3, 2016
-------------------

- Enh #3: `FileManagerPhp::writeData()` now invalidates script file cache performed by 'OPCache' or 'APC' (klimov-paul)
- Bug #2: Fixed `ActiveRecord` looses not 'dirty' data on update (fps01)


1.0.0, December 26, 2015
------------------------

- Initial release.
