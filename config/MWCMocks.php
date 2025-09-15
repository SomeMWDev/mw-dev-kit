<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

namespace MediaWikiConfig;

use GrowthExperiments\NewcomerTasks\AddImage\SubpageImageRecommendationProvider;
use GrowthExperiments\NewcomerTasks\AddLink\SubpageLinkRecommendationProvider;
use GrowthExperiments\NewcomerTasks\ConfigurationLoader\StaticConfigurationLoader;
use GrowthExperiments\NewcomerTasks\Task\Task;
use GrowthExperiments\NewcomerTasks\TaskSuggester\StaticTaskSuggesterFactory;
use GrowthExperiments\NewcomerTasks\TaskSuggester\TaskSuggesterFactory;
use GrowthExperiments\NewcomerTasks\TaskType\LinkRecommendationTaskType;
use GrowthExperiments\NewcomerTasks\TaskType\TaskType;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use TitleValue;

trait MWCMocks {

	public function mockGrowthExperimentsEditSuggestions(): void {
		# Enable under-development features still behind feature flag:
		$this->conf( 'wgGENewcomerTasksLinkRecommendationsEnabled', true );
		$this->conf( 'wgGELinkRecommendationsFrontendEnabled', true );

		$this->autoHook( new class implements \MediaWiki\Hook\MediaWikiServicesHook {
			/**
			 * @inheritDoc
			 */
			public function onMediaWikiServices(
				$services
			): void {
				$linkRecommendationTaskType =
					new LinkRecommendationTaskType( 'link-recommendation', TaskType::DIFFICULTY_EASY, [] );

				# Mock the configuration, which would normally be at MediaWiki:NewcomerTaskConfig.json, to have just
				# one 'link-recommendation' task type.
				$services->redefineService( 'GrowthExperimentsNewcomerTasksConfigurationLoader',
					static function () use ( $linkRecommendationTaskType ) {
						return new StaticConfigurationLoader( [ $linkRecommendationTaskType ] );
					} );

				# Mock the task suggester to specify what article(s) will be suggested.
				$services->redefineService( 'GrowthExperimentsTaskSuggesterFactory',
					static function () use ( $linkRecommendationTaskType ): TaskSuggesterFactory {
						return new StaticTaskSuggesterFactory( [
							new Task( $linkRecommendationTaskType, new TitleValue( NS_MAIN, 'Douglas Adams' ) ),
						] );
					} );
			}
		} );

		# Set up SubpageLinkRecommendationProvider, which will take the recommendation from the article's /addlink.json
		# subpage, e.g. [[Douglas Adams/addlink.json]]. The output of https://addlink-simple.toolforge.org can be
		# copied there.
		$this->hook( 'MediaWikiServices', static fn ( MediaWikiServices $services
		) => SubpageLinkRecommendationProvider::onMediaWikiServices( $services ) );
		$this->hook( 'ContentHandlerDefaultModelFor', static fn ( Title $title, &$model
		) => SubpageLinkRecommendationProvider::onContentHandlerDefaultModelFor( $title, $model ) );
		# Same for image recommendations, with addimage.json and http://image-suggestion-api.wmcloud.org/?doc
		$this->hook( 'MediaWikiServices', static fn ( MediaWikiServices $services
		) => SubpageImageRecommendationProvider::onMediaWikiServices( $services ) );
		$this->hook( 'ContentHandlerDefaultModelFor', static fn ( Title $title, &$model
		) => SubpageImageRecommendationProvider::onContentHandlerDefaultModelFor( $title, $model ) );
	}

}
