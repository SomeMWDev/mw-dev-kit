<?php

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
// phpcs:disable MediaWiki.Files.ClassMatchesFilename.NotMatch
// phpcs:disable Generic.Files.OneObjectStructurePerFile.MultipleFound
// phpcs:disable PSR2.Methods.MethodDeclaration.Underscore

namespace MediaWikiConfig;

use Exception;

enum CodeMirrorVersion {
	case V5;
	case V6;
}

enum RelatedArticlesSource {
	case DESCRIPTION2;
	case SHORTDESCRIPTION;
	case TEXTEXTRACTS;
	case WIKIDATA;
}

trait MWCExtensions {

	private array $extensionFunctionMappings = [
		'Echo' => 'Echo_',
		'3DAlloy' => '_3DAlloy',
	];

	public function loadExtensionOrSkin( string $functionName ): self {
		if ( isset( $this->extensionFunctionMappings[$functionName] ) ) {
			$fName = $this->extensionFunctionMappings[$functionName];
		}
		$fName ??= $functionName;

		return $this->$fName();
	}

	public function _3DAlloy(): self {
		return $this->ext( '3DAlloy' );
	}

	public function AdvancedSearch(): self {
		return $this
			->CirrusSearch()
			->ext( 'AdvancedSearch' );
	}

	public function AJAXPoll(): self {
		return $this->ext( 'AJAXPoll' );
	}

	public function Analytics(): self {
		return $this->ext( 'Analytics' );
	}

	public function ApprovedRevs(): self {
		return $this->ext( 'ApprovedRevs' );
	}

	public function ArticleFeedbackv5(): self {
		return $this->ext( 'ArticleFeedbackv5' );
	}

	public function ArticleSummaries(): self {
		return $this
			->MinervaNeue( true )
			->ext( 'ArticleSummaries' );
	}

	public function AutoCreateCategoryPages(): self {
		return $this->ext( 'AutoCreateCategoryPages' );
	}

	public function AutoCreatePage(): self {
		return $this->ext( 'AutoCreatePage' );
	}

	public function Bootstrap(): self {
		return $this->ext( 'Bootstrap' );
	}

	public function Bucket( string $dbUsername = 'bucket', string $dbPassword = 'bucket_password' ): self {
		return $this
			->ext( 'Bucket' )
			->conf( 'wgBucketDBuser', $dbUsername )
			->conf( 'wgBucketDBpassword', $dbPassword );
	}

	public function CategoryTree(): self {
		return $this->ext( 'CategoryTree' );
	}

	public function Cargo(): self {
		return $this->ext( 'Cargo' );
	}

	public function CentralNotice(): self {
		return $this
			->ext( 'CentralNotice' )
			->conf( 'wgNoticeInfrastructure', true )
			->conf( 'wgNoticeProject', 'centralnoticeproject' )
			->conf( 'wgNoticeProjects', [ $this->getConf( 'wgNoticeProject' ) ] )
			->cloneConf( 'wgCentralHost', 'wgServer' )
			->conf( 'wgCentralSelectedBannerDispatcher',
				$this->getConf( 'wgServer' ) . $this->getConf( 'wgScriptPath' ) .
				'/index.php?title=Special:BannerLoader' )
			->cloneConf( 'wgCentralDBname', 'wgDBname' )
			->conf( 'wgCentralNoticeGeoIPBackgroundLookupModule', 'ext.centralNotice.freegeoipLookup' );
	}

	public function Chart(): self {
		return $this->ext( 'Chart' );
	}

	public function CheckUser(): self {
		return $this
			->ext( 'CheckUser' )
			->grantPermissions(
				'sysop',
				'checkuser', 'checkuser-log', 'investigate',
				'checkuser-temporary-account-log', 'checkuser-temporary-account-no-preference'
			);
	}

	public function ChessBrowser(): self {
		return $this->ext( 'ChessBrowser' );
	}

	public function CirrusSearch(): self {
		// note: this sets the default skin to vector 2022
		require_once $this->extensionFilePath( 'CirrusSearch', 'tests/jenkins/FullyFeaturedConfig.php' );
		if ( !defined( 'MW_PHPUNIT_TEST' ) ) {
			$this->conf( 'wgCirrusSearchServers', [
				[
					'transport' => CirrusSearch\Elastica\DeprecationLoggedHttp::class,
					"host" => "elasticsearch",
				],
			] );
		}

		return $this
			->Elastica()
			->ext( 'CirrusSearch' );
	}

	public function Cite(): self {
		return $this->ext( 'Cite' );
	}

	public function CodeMirror( CodeMirrorVersion $version = CodeMirrorVersion::V6 ): self {
		if ( $version === CodeMirrorVersion::V6 ) {
			$this->conf( 'wgCodeMirrorV6', true );
		}

		return $this
			->ext( 'CodeMirror' )
			->defaultUserOption( 'usecodemirror', true );
	}

	public function ColorizerToolVe(): self {
		return $this->ext( 'ColorizerToolVe' );
	}

	public function Commentbox(): self {
		return $this->ext( 'Commentbox' );
	}

	public function CommentStreams( array|int $allowedNamespaces = -1 ): self {
		return $this
			->ext( 'CommentStreams' )
			->conf( 'wgAllowDisplayTitle', true )
			->conf( 'wgRestrictDisplayTitle', false )
			->conf( 'wgCommentStreamsAllowedNamespaces', $allowedNamespaces );
	}

	public function CommunityConfiguration(): self {
		return $this->ext( 'CommunityConfiguration' );
	}

	public function CommunityRequests( bool $optionalDependencies = false ): self {
		if ( $optionalDependencies ) {
			$this
				->Translate()
				->WikimediaMessages();
		}

		return $this
			->ext( 'CommunityRequests' )
			->conf( 'wgPageLanguageUseDB', true );
	}

	public function ConfirmAccount(): self {
		return $this
			->ext( 'ConfirmAccount' )
			->revokePermission( '*', 'createaccount' );
	}

	public function ContentTranslation( string $cxServer ): self {
		$mwServer = $this->getConf( 'wgServer' );
		$this
			->ext( 'ContentTranslation' )
			->modConf( 'wgContentTranslationSiteTemplates', static function ( &$c ) use ( $cxServer, $mwServer ) {
				$c['cx'] = $cxServer;
				// TODO don't hardcode article path etc
				$c['view'] = "$mwServer/wiki/$2";
				$c['action'] = "$mwServer/w/index.php?title=$2";
				$c['api'] = "$mwServer/w/api.php";
			} );
		return $this;
	}

	public function Contributors(): self {
		return $this->ext( 'Contributors' );
	}

	public function CookieConsent(): self {
		return $this->ext( 'CookieConsent' );
	}

	public function CountDownClock(): self {
		return $this->ext( 'CountDownClock' );
	}

	public function CreateAPage(): self {
		return $this->ext( 'CreateAPage' );
	}

	public function CreatePage(): self {
		return $this->ext( 'CreatePage' );
	}

	public function CSS(): self {
		return $this->ext( 'CSS' );
	}

	public function DataMaps(): self {
		return $this->ext( 'DataMaps' );
	}

	public function DataTransfer(): self {
		return $this
			->ext( 'DataTransfer' )
			->grantPermission( 'user', 'datatransferimport' );
	}

	public function Description2(): self {
		return $this
			->ext( 'Description2' )
			->conf( 'wgEnableMetaDescriptionFunctions', true );
	}

	public function Diagrams(): self {
		return $this->ext( 'Diagrams' );
	}

	public function DiscordNotifications( array $additionalWebhookUrls = [] ): self {
		return $this
			->ext( 'DiscordNotifications' )
			->conf( 'wgDiscordIncomingWebhookUrl', $this->env( 'DISCORD_WEBHOOK_URL' ) )
			->cloneConf( to: 'wgDiscordFromName', from: 'wgSitename' )
			->conf( 'wgDiscordAvatarUrl', '' )
			->conf( 'wgDiscordNotificationWikiUrl', $this->env( 'MW_SERVER' ) . '/' )
			->conf( 'wgDiscordAdditionalIncomingWebhookUrls', $additionalWebhookUrls );
	}

	public function DiscussionTools(): self {
		return $this->ext( 'DiscussionTools' );
	}

	public function DPLforum(): self {
		return $this->ext( 'DPLforum' );
	}

	public function DynamicPageList3(): self {
		return $this->ext( 'DynamicPageList3' );
	}

	public function DynamicPageList4(): self {
		return $this->ext( 'DynamicPageList4' );
	}

	public function Echo_(): self {
		return $this->ext( 'Echo' );
	}

	public function EditSubpages(): self {
		return $this->ext( 'EditSubpages' );
	}

	public function Elastica(): self {
		return $this->ext( 'Elastica' );
	}

	public function EmbedVideo(): self {
		return $this->ext( 'EmbedVideo' );
	}

	public function EntitySchema(): self {
		return $this->ext( 'EntitySchema' );
	}

	public function EventLogging(): self {
		return $this->ext( 'EventLogging' );
	}

	public function ExternalData(): self {
		return $this->ext( 'ExternalData' );
	}

	public function FlexDiagrams(): self {
		return $this->ext( 'FlexDiagrams' );
	}

	public function FloatingUI(): self {
		return $this->ext( 'FloatingUI' );
	}

	public function FontAwesome(): self {
		return $this->ext( 'FontAwesome' );
	}

	public function GlobalUserPage( string $apiUrl ): self {
		return $this
			->ext( 'GlobalUserPage' )
			->conf( 'wgGlobalUserPageAPIUrl', $apiUrl );
	}

	public function GrowthExperiments(): self {
		// https://www.mediawiki.org/wiki/Extension:GrowthExperiments/developer_setup
		return $this
			->Vector()
			->MinervaNeue()
			->CirrusSearch()
			->Echo_()
			->Elastica()
			->PageViewInfo()
			->MobileFrontend()
			->VisualEditor()
			->WikimediaMessages()
			->CommunityConfiguration()
			->ext( 'GrowthExperiments' )
			->conf( 'wgGEDeveloperSetup', true );
	}

	public function GuidedTour(): self {
		return $this->ext( 'GuidedTour' );
	}

	public function HAWelcome(): self {
		return $this->ext( 'HAWelcome' );
	}

	public function ImageRating(): self {
		return $this
			->VoteNY()
			->ext( 'ImageRating' );
	}

	public function ImgTag(): self {
		return $this->ext( 'ImgTag' );
	}

	public function InputBox(): self {
		return $this->ext( 'InputBox' );
	}

	public function InterwikiDispatcher(): self {
		return $this->ext( 'InterwikiDispatcher' );
	}

	public function JsonConfig(): self {
		return $this->ext( 'JsonConfig' );
	}

	public function LanguageSelector( bool $limitLangs = false ): self {
		if ( $limitLangs ) {
			$this->conf( 'wgLanguageSelectorLanguages', [ 'en', 'es', 'pt' ] );
		}

		return $this->ext( 'LanguageSelector' );
	}

	public function LastModified(): self {
		return $this->ext( 'LastModified' );
	}

	public function LazyParse(): self {
		return $this->ext( 'LazyParse' );
	}

	public function LinkCards(): self {
		return $this->ext( 'LinkCards' );
	}

	public function LockAuthor(): self {
		return $this->ext( 'LockAuthor' );
	}

	public function Lockdown(): self {
		return $this->ext( 'Lockdown' );
	}

	public function Maps(): self {
		return $this->ext( 'Maps' );
	}

	public function MassEditRegex(): self {
		return $this
			->ext( 'MassEditRegex' )
			->grantPermission( 'sysop', 'masseditregex' );
	}

	public function Mermaid(): self {
		return $this->ext( 'Mermaid' );
	}

	public function MobileFrontend(): self {
		return $this->ext( 'MobileFrontend' );
	}

	public function Moderation(): self {
		return $this->ext( 'Moderation' );
	}

	public function Monstranto(): self {
		return $this->ext( 'Monstranto' );
	}

	public function MsUpload(): self {
		return $this
			->WikiEditor()
			->ext( 'MsUpload' );
	}

	public function MultiBoilerplate(
		array $boilerplates = [ 'My Boilerplate' => 'Template:My Boilerplate' ]
	): self {
		return $this
			->ext( 'MultiBoilerplate' )
			->modConf( 'wgMultiBoilerplateOptions', static function ( &$c ) use ( $boilerplates ) {
				$c += $boilerplates;
			} );
	}

	public function MWDevHelper(): self {
		return $this->ext( 'MWDevHelper' );
	}

	public function NamespacePaths(): self {
		return $this->ext( 'NamespacePaths' );
	}

	public function Network(): self {
		return $this->ext( 'Network' );
	}

	public function Newsletter(): self {
		return $this->ext( 'Newsletter' );
	}

	public function Nuke(): self {
		return $this->ext( 'Nuke' );
	}

	public function NukeDPL(): self {
		return $this
			// not a requirement in extension.json, so load manually
			// TODO use DPL4?
			->DynamicPageList3()
			->NukeDPL();
	}

	public function OAuth(): self {
		return $this->ext( 'OAuth' );
	}

	public function OreDict(): self {
		return $this
			->ext( 'OreDict' )
			// yes, the tilesheet ones are in OreDict's extension.json too for some reason
			->grantPermissions( 'sysop', 'editoredict', 'importoredict', 'edittilesheets', 'importtilesheets' );
	}

	public function OrphanedTalkPages(): self {
		return $this->ext( 'OrphanedTalkPages' );
	}

	public function PageForms(): self {
		return $this->ext( 'PageForms' );
	}

	public function PageImages(): self {
		return $this->ext( 'PageImages' );
	}

	public function PageSync(): self {
		return $this->ext( 'PageSync' );
	}

	public function PageTriage(): self {
		return $this->ext( 'PageTriage' );
	}

	public function PageViewInfo(): self {
		return $this
			->ext( 'PageViewInfo' )
			->conf( 'wgPageViewInfoWikimediaDomain', 'en.wikipedia.org' );
	}

	public function ParserFunctions(): self {
		return $this->ext( 'ParserFunctions' );
	}

	public function ParserMigration(
		bool $wikiWideParsoid = false
	): self {
		if ( $wikiWideParsoid ) {
			$this
				->conf( 'wgParserMigrationEnableParsoidArticlePages', true )
				->conf( 'wgParserMigrationEnableParsoidDiscussionTools', true );
		}
		return $this->ext( 'ParserMigration' );
	}

	public function ParserPower(): self {
		return $this->ext( 'ParserPower' );
	}

	public function Poem(): self {
		return $this->ext( 'Poem' );
	}

	public function PollNY(): self {
		return $this
			->SocialProfile()
			->ext( 'PollNY' );
	}

	public function Preloader( string $mainSource = 'Template:Boilerplate' ): self {
		return $this
			->ext( 'Preloader' )
			->modConf( 'wgPreloaderSource', static function ( &$c ) use ( $mainSource ) {
				$c[NS_MAIN] = $mainSource;
			} );
	}

	public function ProofreadPage(): self {
		return $this->ext( 'ProofreadPage' );
	}

	public function QuizGame(): self {
		return $this
			->SocialProfile()
			->ext( 'QuizGame' );
	}

	public function RatePage(): self {
		return $this->ext( 'RatePage' );
	}

	public function RelatedArticles(
		RelatedArticlesSource $source, bool $optionalDependencies = false
	): self {
		if ( $optionalDependencies ) {
			$this
				->EventLogging()
				->PageImages();
		}

		$confName = 'wgRelatedArticlesDescriptionSource';
		match ( $source ) {
			RelatedArticlesSource::DESCRIPTION2 => $this->conf( $confName, 'pagedescription' )->Description2(),
			RelatedArticlesSource::SHORTDESCRIPTION => $this->conf( $confName, 'wikidata' )->ShortDescription(),
			RelatedArticlesSource::TEXTEXTRACTS => $this->conf( $confName, 'textextracts' )->TextExtracts(),
			RelatedArticlesSource::WIKIDATA => $this->conf( $confName, 'wikidata' )->WikibaseClient(),
		};

		return $this->ext( 'RelatedArticles' );
	}

	public function ReplaceText(): self {
		return $this->ext( 'ReplaceText' );
	}

	public function RightFunctions(): self {
		return $this->ext( 'RightFunctions' );
	}

	public function RobloxAPI(): self {
		return $this->ext( 'RobloxAPI' );
	}

	public function Rules(): self {
		return $this->ext( 'Rules' );
	}

	public function Screenplay(): self {
		return $this->ext( 'Screenplay' );
	}

	public function Scribunto(): self {
		return $this->ext( 'Scribunto' );
	}

	public function SearchThumbs(): self {
		return $this
			->PageImages()
			->ext( 'SearchThumbs' );
	}

	public function SecurePoll(): self {
		return $this
			->ext( 'SecurePoll' )
			->grantPermissions(
				'sysop',
				'securepoll-create-poll', 'securepoll-edit-poll', 'securepoll-view-voter-pii'
			);
	}

	public function SemanticDrilldown(): self {
		return $this
			// not specified as a requirement in extension.json
			->SemanticMediaWiki()
			->ext( 'SemanticDrilldown' );
	}

	public function SemanticMediaWiki(): self {
		return $this
			->ext( 'SemanticMediaWiki' )
			->disableSQLStrictMode();
	}

	public function SemanticScribunto(): self {
		return $this->ext( 'SemanticScribunto' );
	}

	public function SimpleBlogPage(): self {
		return $this->ext( 'SimpleBlogPage' );
	}

	public function SimpleTooltip(): self {
		return $this->ext( 'SimpleTooltip' );
	}

	/**
	 * IMPORTANT: You need to call `require_once $c->extensionFilePath( 'SocialProfile', 'SocialProfile.php' );` in
	 * LocalSettings.php in addition to calling this function!
	 * @return MediaWikiConfig|MWCExtensions
	 */
	public function SocialProfile(): self {
		if ( !isset( $this->getConf( 'wgMessagesDirs' )['SocialProfile'] ) ) {
			throw new Exception( 'Please require SocialProfile.php before calling MWCExtensions::SocialProfile!' );
		}
		return $this
			->modConf( 'wgUserProfileDisplay', static function ( &$c ) {
				$c['board'] = true;
				$c['foes'] = true;
				$c['friends'] = true;
				// If set to false, disables both avatar display and upload
				$c['avatar'] = true;
			} )
			->conf( 'wgUserBoard', true )
			->conf( 'wgFriendingEnabled', true )
			->cloneConf( to: 'wgAvatarKey', from: 'wgDBname' )
			->conf( 'wgUserPageChoice', true )
			->conf( 'wgUserProfileAvatarsInDiffs', true )
			->grantPermission( 'sysop', 'generatetopusersreport' )
			->modConf( 'wgUserStatsPointValues', static function ( &$c ) {
				$c['points_winner_weekly'] = 1;
			} );
	}

	public function Springboard(): self {
		require_once $this->extensionFilePath( 'Springboard', 'includes/CustomLoader.php' );

		return $this->ext( 'Springboard' );
	}

	public function SyntaxHighlight_GeSHi(): self {
		return $this->ext( 'SyntaxHighlight_GeSHi' );
	}

	public function TabberNeue(): self {
		return $this->ext( 'TabberNeue' );
	}

	public function TableProgressTracking(): self {
		return $this->ext( 'TableProgressTracking' );
	}

	public function Tabs(): self {
		return $this->ext( 'Tabs' );
	}

	public function TemplateData(): self {
		return $this->ext( 'TemplateData' );
	}

	public function TemplateStyles(): self {
		return $this->ext( 'TemplateStyles' );
	}

	public function TextExtracts(): self {
		return $this->ext( 'TextExtracts' );
	}

	public function Tilesheets(): self {
		return $this
			// requirement not specified in extension.json
			->OreDict()
			->ext( 'Tilesheets' )
			->grantPermissions( 'sysop', 'edittilesheets', 'importtilesheets', 'translatetiles' );
	}

	public function TimedMediaHandler(): self {
		return $this->ext( 'TimedMediaHandler' );
	}

	public function TitleBlacklist(): self {
		return $this->ext( 'TitleBlacklist' );
	}

	public function Translate(): self {
		return $this->ext( 'Translate' );
	}

	public function UniversalLanguageSelector(): self {
		return $this->ext( 'UniversalLanguageSelector' );
	}

	public function UnusedRedirects(): self {
		return $this->ext( 'UnusedRedirects' );
	}

	public function UploadWizard(): self {
		return $this
			->enableUploads()
			->ext( 'UploadWizard' );
	}

	public function UserFunctions( array $namespaces = [] ): self {
		return $this
			->ext( 'UserFunctions' )
			->conf( 'wgUFAllowedNamespaces', $namespaces );
	}

	public function UserVerification(): self {
		return $this->ext( 'UserVerification' );
	}

	public function Video(): self {
		return $this->ext( 'Video' );
	}

	public function VisualEditor(): self {
		return $this->ext( 'VisualEditor' );
	}

	public function VoteNY(): self {
		return $this->ext( 'VoteNY' );
	}

	public function WatchAnalytics(): self {
		return $this->ext( 'WatchAnalytics' );
	}

	public function WikibaseRepository(): self {
		return $this->ext( 'WikibaseRepository', $this->extensionFilePath( 'Wikibase', 'extension-repo.json' ) );
	}

	public function WikibaseClient(): self {
		return $this
			// load repo - easier to test locally this way (see change message at
			// https://gerrit.wikimedia.org/r/c/mediawiki/extensions/Wikibase/+/933906)
			->WikibaseRepository()
			->ext( 'WikibaseClient', $this->extensionFilePath( 'Wikibase', 'extension-client.json' ) );
	}

	public function wikihiero(): self {
		return $this->ext( 'wikihiero' );
	}

	public function WikimediaMessages(): self {
		return $this->ext( 'WikimediaMessages' );
	}

	public function Wikistories(): self {
		return $this
			->enableInstantCommons()
			// all three not specified as requirements in extension.json
			->MinervaNeue()
			->EventLogging()
			->MobileFrontend()
			->ext( 'Wikistories' )
			->conf( 'wgWikistoriesDiscoveryMode', 'public' )
			->conf( 'wgWikistoriesRestDomain', 'wikipedia.org' );
	}

	public function WikiEditor(): self {
		return $this
			->ext( 'WikiEditor' )
			->modConf( 'wgHiddenPrefs', static fn ( &$c ) => $c[] = 'usebetatoolbar' )
			->defaultUserOption( 'usebetatoolbar', 1 );
	}

	public function WikiForum(): self {
		return $this->ext( 'WikiForum' );
	}

	public function WikiLambda( ?string $orchestratorUrl = null ): self {
		$composeProjectName = $orchestratorUrl !== null ? '' : $this->env( 'DOCKER_COMPOSE_PROJECT_NAME' );
		/** @noinspection HttpUrlsUsage communication between docker containers */
		return $this
			// VE is optional but quite useful
			->VisualEditor()
			->ext( 'WikiLambda' )
			->conf( 'wgWikiLambdaOrchestratorLocation', $orchestratorUrl ??
				"http://$composeProjectName-function-orchestrator-1:6254/1/v1/evaluate" )
			->conf( 'wgWikiLambdaEnableRepoMode', true )
			->conf( 'wgWikiLambdaEnableClientMode', true );
	}

	public function WikiLove(): self {
		return $this->ext( 'WikiLove' );
	}

	public function WikiPoints(): self {
		return $this->ext( 'WikiPoints' );
	}

	public function WikiSEO(): self {
		return $this->ext( 'WikiSEO' );
	}

	// BLUESPICE EXTENSIONS

	public function BlueSpiceFoundation(): self {
		return $this
			->ExtJSBase()
			->ext( 'BlueSpiceFoundation' );
	}

	public function BlueSpiceWhoIsOnline(): self {
		return $this
			// strict mode is seemingly unsupported by mwstake-mediawiki-component-datastore
			->disableSQLStrictMode()
			->ext( 'BlueSpiceWhoIsOnline' );
	}

	public function CognitiveProcessDesigner(): self {
		return $this->ext( 'CognitiveProcessDesigner' );
	}

	public function ExtJSBase(): self {
		return $this->ext( 'ExtJSBase' );
	}

	public function NumberHeadings(): self {
		return $this
			->ext( 'NumberHeadings' )
			->conf( 'wgNumberHeadingsEnable', true );
	}

	public function OOJSPlus(): self {
		return $this->ext( 'OOJSPlus' );
	}

}
