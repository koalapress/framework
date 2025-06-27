<?php

namespace KoalaPress\FlexibleContent\Finder;

use Illuminate\Support\Str;

class LayoutFinder
{
    /**
     * Find and return the layouts for flexible content.
     *
     * @return array
     */
    static public function find(): array
    {
        $fieldGroups = collect(acf_get_field_groups());

        $sectionOptions = $fieldGroups
            ->filter(function ($group) {
                return collect($group['location'])
                    ->contains(function ($location) {
                        return collect($location)
                            ->contains(function ($rule) {
                                return $rule['param'] === 'flexible_content' && $rule['operator'] === '==' && $rule['value'] === 'section_options';
                            });
                    });
            })
            ->map(function ($group) {
                return $group['key'];
            })
            ->toArray();

        return $fieldGroups
            ->filter(function ($group) {
                return collect($group['location'])
                    ->contains(function ($location) {
                        return collect($location)
                            ->contains(function ($rule) {
                                return $rule['param'] === 'flexible_content' && $rule['operator'] === '==' && $rule['value'] === 'section';
                            });
                    });
            })
            ->mapWithKeys(function ($fieldGroup) use ($sectionOptions) {
                $key = Str::chopStart($fieldGroup['key'], 'group_');

                $subFields = [
                    [
                        'key' => "field_{$key}",
                        'type' => 'clone',
                        'required' => 0,
                        'clone' => [
                            $fieldGroup['key'],
                        ],
                        'acfe_seamless_style' => 0,
                        'acfe_clone_modal' => 0,
                        'acfe_clone_modal_close' => 0,
                        'acfe_clone_modal_button' => '',
                        'acfe_clone_modal_size' => 'large',
                    ],
                ];

                if (!empty($sectionOptions)) {
                    array_unshift($subFields, [
                        'key' => "field_tab_1_{$key}",
                        'label' => 'Inhalt',
                        'name' => '',
                        'aria-label' => '',
                        'type' => 'tab',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'placement' => 'top',
                        'endpoint' => 0,
                        'selected' => 1,
                    ]);
                    $subFields[] = [
                        'key' => "field_tab_2_{$key}",
                        'label' => 'Sektionsoptionen',
                        'name' => '',
                        'aria-label' => '',
                        'type' => 'tab',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'placement' => 'top',
                        'endpoint' => 0,
                        'selected' => 0,
                    ];
                    $subFields[] = [
                        'key' => "field_section_options_{$key}",
                        'label' => '',
                        'type' => 'clone',
                        'required' => 0,
                        'clone' => $sectionOptions,
                        'acfe_seamless_style' => 0,
                        'acfe_clone_modal' => 0,
                        'acfe_clone_modal_close' => 0,
                        'acfe_clone_modal_button' => '',
                        'acfe_clone_modal_size' => 'large',
                    ];
                }

                return [
                    $key => [
                        'key' => 'layout_' . $fieldGroup['key'],
                        'name' => $key,
                        'label' => $fieldGroup['acfe_display_title'] ?? $fieldGroup['title'],
                        'display' => 'block',
                        'sub_fields' => $subFields,
                        'min' => '',
                        'max' => '',
                        'acfe_flexible_render_template' => false,
                        'acfe_flexible_render_style' => false,
                        'acfe_flexible_render_script' => false,
                        'acfe_flexible_thumbnail' => false,
                        'acfe_flexible_settings' => false,
                        'acfe_flexible_settings_size' => 'medium',
                        'acfe_flexible_modal_edit_size' => false,
                        'acfe_flexible_category' => false,
                    ],
                ];
            })
            ->filter()
            ->toArray();
    }
}
