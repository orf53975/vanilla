<?php
/**
 *
 * @author Isis Graziatto <isis.g@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

if (!defined('APPLICATION')) {
    exit();
}

/**
 * Class KeystoneThemeHooks
 */
class KeystoneThemeHooks extends Gdn_Plugin {

    /**
     * Run once on enable.
     *
     * @return void
     */
    public function setup() {
        $this->structure();
    }

    /**
     * Run on utility/update.
     *
     * @return void
     */
    public function structure() {
        saveToConfig([
            'Garden.MobileTheme' => 'keystone',
            'Badges.BadgesModule.Target' => 'AfterUserInfo',
            'Feature.NewFlyouts.Enabled' => true,
            'Garden.ThemeOptions.Styles.Key' => 'Default',
            'Garden.ThemeOptions.Styles.Value' => '%s_default',
            'Garden.ThemeOptions.Options.hasHeroBanner' => false,
            'Garden.ThemeOptions.Options.hasFeatureSearchbox' => false,
            'Garden.ThemeOptions.Options.panelToLeft' => false,
        ]);
    }

    /**
     * Runs every page load
     *
     * @param Gdn_Controller $sender This could be any controller
     *
     * @return void
     */
    public function base_render_before($sender) {
        if (inSection('Dashboard')) {
            return;
        }

        // Set Data "heroImageUrl" to smarty
        if (class_exists('HeroImagePlugin')) {
            $imageUrl = HeroImagePlugin::getCurrentHeroImageLink();
            $sender->setData('heroImageUrl', $imageUrl);
        }

        $hasAdvancedSearch = class_exists('AdvancedSearchPlugin');

        //set "hasAdvancedSearch" to smarty
        $sender->setData('hasAdvancedSearch', $hasAdvancedSearch);

        //unset config ThemeOptions.Options.hasFeatureSearchbox if AdvancedSearchPlugin is disabled
        if (!$hasAdvancedSearch) {
            saveToConfig([
                'Garden.ThemeOptions.Options.hasFeatureSearchbox' => false,
            ]);
        }

        //set ThemeOptions to smarty
        $themeOptions = c("Garden.ThemeOptions");

        foreach ($themeOptions as $key => &$value) {
            $sender->setData("ThemeOptions.".$key, $value);
        }
    }

    /**
     * Add custom toggles "hasHeroBanner", "hasFeatureSearchbox", "panelToLeft" to Theme Options
     *
     * @param settingsController $sender
     *
     * @return void
     */
    public function settingsController_afterCustomStyles_handler($sender) {
        $form = $sender->Form;
        $hasHeroImagePlugin = $sender->Data["hasHeroImagePlugin"];
        $hasAdvancedSearch = $sender->Data["hasAdvancedSearch"];
        echo '<section>';
        echo    '<h2 class="subheading">'.t("Options").'</h2>';

        //Only render these fields if hasHeroImagePlugin == true
        if ($hasHeroImagePlugin) {
            echo    '<li class="form-group">';
            echo        $sender->Form->toggle(
                "ThemeOptions.Options.hasHeroBanner",
                t("Integrate Hero Image plugin"),
                [],
                t("Displays \"Hero Image\" plugin below the header. \"Hero Image\" plugin needs to be enabled for this option to work properly.")
            );
            echo    '</li>';

            //Only render this field if AdvancedSearchPlugin == true
            if ($hasAdvancedSearch) {
                echo    '<li class="form-group">';
                echo        $sender->Form->toggle(
                    "ThemeOptions.Options.hasFeatureSearchbox",
                    t("Integrate searchbox with Hero Image plugin"),
                    [],
                    t("Change searchbox's position to display over Hero Banner. \"Hero Image\" plugin needs to be enabled for this option to work properly.")
                );
                echo    '</li>';
            }
        }

        echo    '<li class="form-group">';
        echo        $sender->Form->toggle("ThemeOptions.Options.panelToLeft", t("Panel to the left"), [], t("Change the main panel's position to the left side."));
        echo    '</li>';
        echo '</section>';
        echo '<div class="form-footer js-modal-footer">';
        echo    $form->button('Save');
        echo '</div>';
    }

    /**
     * Unset ThemeOptions.Options config related to HeroImagePlugin
     *
     * @param gdn_pluginManager $sender
     * @param array $args
     *
     * @return void
     */
    public function gdn_pluginManager_addonDisabled_handler($sender, $args) {
        if ($args["AddonName"] === "heroimage") {
            saveToConfig([
                'Garden.ThemeOptions.Options.hasHeroBanner' => false,
                'Garden.ThemeOptions.Options.hasFeatureSearchbox' => false,
            ]);
        }
    }

    /**
     * Register {searchbox_advanced} even if AdvancedSearchPlugin is disabled so theme doens't break
     *
     * @param Smarty $sender
     */
    public function gdn_smarty_init_handler($sender) {
        if (!class_exists('AdvancedSearchPlugin')) {
            $sender->register_function('searchbox_advanced', 'searchBoxAdvancedMock');
        }
    }

    /**
     * Add support to `hasHeroBanner`, `hasFeatureSearchbox` and `panelToLeft` custom fields
     *
     * @param SettingsController $sender
     */
    public function settingsController_themeOptions_create($sender) {
        $sender->permission('Garden.Settings.Manage');
        $form = $sender->Form;

        $sender->addJsFile('addons.js');
        $sender->setHighlightRoute('dashboard/settings/themeoptions');

        $themeManager = Gdn::themeManager();
        $sender->setData('ThemeInfo', $themeManager->enabledThemeInfo());

        // set hasHeroImagePlugin to view
        $sender->setData('hasHeroImagePlugin', class_exists('HeroImagePlugin'));
        $sender->setData('hasAdvancedSearch', class_exists('AdvancedSearchPlugin'));

        //get toggle values from config
        $checkboxes = c("Garden.ThemeOptions.Options");

        foreach ($checkboxes as $key => $value) {
            $form->setValue("ThemeOptions.Options.".$key, $value);
        }

        if ($form->authenticatedPostBack()) {
            // Save the styles to the config.
            $styleKey = $form->getFormValue('StyleKey');

            $configSaveData = [
                'Garden.ThemeOptions.Styles.Key' => $styleKey,
                'Garden.ThemeOptions.Styles.Value' => $sender->data("ThemeInfo.Options.Styles.$styleKey.Basename")];

            // Save the text to the locale.
            foreach ($sender->data('ThemeInfo.Options.Text', []) as $key => $default) {
                $value = $form->getFormValue($form->escapeFieldName('Text_'.$key));
                $configSaveData["ThemeOption.{$key}"] = $value;
            }

            foreach ($form->_FormValues["Checkboxes"] as $key => $fieldName) {
                $value = $form->getFormValue($fieldName) === false ? false : true;
                $configSaveData["Garden.{$fieldName}"] = $value;
            }

            saveToConfig($configSaveData);
            $sender->informMessage(t("Your changes have been saved."));
        }

        $sender->setData('ThemeOptions', c('Garden.ThemeOptions'));
        $styleKey = $sender->data('ThemeOptions.Styles.Key');

        if (!$form->isPostBack()) {
            foreach ($sender->data('ThemeInfo.Options.Text', []) as $key => $options) {
                $default = val('Default', $options, '');
                $value = c("ThemeOption.{$key}", '#DEFAULT#');
                if ($value === '#DEFAULT#') {
                    $value = $default;
                }

                $form->setValue($form->escapeFieldName('Text_'.$key), $value);
            }
        }

        $sender->setData('ThemeFolder', $themeManager->getEnabledDesktopThemeKey());
        $sender->title(t('Theme Options'));
        $form->addHidden('StyleKey', $styleKey);

        $sender->render();
    }
}

if (!function_exists('searchBoxAdvancedMock')) :

    /**
     * Fallback function so theme doesn't break with {searchbox_advanced} declaration
     *
     * @param Smarty $params
     */
    function searchBoxAdvancedMock($params) {
        return "";
    }

endif;
