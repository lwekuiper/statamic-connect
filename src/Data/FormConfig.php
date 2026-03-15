<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicConnect\Data;

use Lwekuiper\StatamicConnect\Facades;
use Statamic\Contracts\Data\Localization;
use Statamic\Contracts\Forms\Form;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasOrigin;
use Statamic\Facades\Form as FormFacade;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class FormConfig implements Localization
{
    use ContainsData;
    use ExistsAsFile;
    use FluentlyGetsAndSets;
    use HasOrigin;

    protected $integration;

    protected $form;

    protected $locale;

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
    }

    public function integration($integration = null)
    {
        return $this->fluentlyGetOrSet('integration')->args(func_get_args());
    }

    public function form($form = null)
    {
        return $this->fluentlyGetOrSet('form')
            ->getter(function ($form) {
                return $form instanceof Form ? $form : FormFacade::find($form);
            })
            ->args(func_get_args());
    }

    public function locale($locale = null)
    {
        return $this->fluentlyGetOrSet('locale')->args(func_get_args());
    }

    public function id()
    {
        return $this->integration().'::'.$this->handle().'::'.$this->locale();
    }

    public function handle()
    {
        return $this->form instanceof Form ? $this->form->handle() : $this->form;
    }

    public function title()
    {
        return $this->form()->title();
    }

    public function emailField($value = null)
    {
        if (func_num_args() === 0) {
            return $this->get('email_field');
        }

        return $this->set('email_field', $value);
    }

    public function consentField($value = null)
    {
        if (func_num_args() === 0) {
            return $this->get('consent_field');
        }

        return $this->set('consent_field', $value);
    }

    public function listIds($value = null)
    {
        if (func_num_args() === 0) {
            return $this->get('list_ids', []);
        }

        return $this->set('list_ids', $value);
    }

    public function tagIds($value = null)
    {
        if (func_num_args() === 0) {
            return $this->get('tag_ids', []);
        }

        return $this->set('tag_ids', $value);
    }

    public function mergeFields($value = null)
    {
        if (func_num_args() === 0) {
            return $this->get('merge_fields', []);
        }

        return $this->set('merge_fields', $value);
    }

    public function contactProperties($value = null)
    {
        if (func_num_args() === 0) {
            return $this->get('contact_properties', []);
        }

        return $this->set('contact_properties', $value);
    }

    public function origin($origin = null)
    {
        if (func_num_args() > 0) {
            throw new \Exception('Origin is determined by site configuration.');
        }

        if (! $this->locale()) {
            return null;
        }

        return $this->getOriginByString(
            app(AddonConfig::class)->originFor($this->locale())
        );
    }

    public function getOriginByString($origin)
    {
        if (! $origin) {
            return null;
        }

        return Facades\FormConfig::find($this->integration(), $this->handle(), $origin);
    }

    public function path()
    {
        $storeKey = $this->integration().'-form-configs';

        return vsprintf('%s/%s%s.%s', [
            rtrim(Stache::store($storeKey)->directory(), '/'),
            Site::multiEnabled() ? $this->locale().'/' : '',
            $this->handle(),
            'yaml',
        ]);
    }

    public function editUrl()
    {
        return $this->cpUrl('connect.'.$this->integration().'.form-config.edit');
    }

    public function updateUrl()
    {
        return $this->cpUrl('connect.'.$this->integration().'.form-config.update');
    }

    public function deleteUrl()
    {
        return $this->cpUrl('connect.'.$this->integration().'.form-config.destroy');
    }

    protected function cpUrl($route)
    {
        $params = [$this->handle()];

        if (Site::multiEnabled()) {
            $params['site'] = $this->locale();
        }

        return cp_route($route, $params);
    }

    public function save()
    {
        return Facades\FormConfig::save($this);
    }

    public function delete()
    {
        return Facades\FormConfig::delete($this);
    }

    public function site()
    {
        return Site::get($this->locale());
    }

    public function fileData()
    {
        return $this->data()->all();
    }

    protected function shouldRemoveNullsFromFileData()
    {
        return ! $this->hasOrigin();
    }
}
