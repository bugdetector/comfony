Comfony is a boilerplate created using symfony 7.3, turbo, stimulus.
Use PHP 8.4 coding standard.


# Entity definition

- Use broadcast attibute if the entity updates should be streamed using mercure protocol.
- File storages should be defined using `App\Entity\File\File` entity.
- If an entity has many files definition should 'Many to Many' connection with File entity.
- File entity should not changed if it is not neccesseary.
- Setter methods should accept nullable parameters always because LiveComponent's require this.
- Validations should be defines using `Symfony\Component\Validator\Constraints` namespace.
- Do not define validator if not neccessary.
- Do not define migration file, comfony created database structure automatically. Just offer to run 'php bin/console config:import' if any database changes required.
```php
<?php

namespace App\Entity\Page;

use App\Entity\File\File;
use App\Repository\Page\PageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Mapping\Annotation\Slug;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[Broadcast(
    topics: ['pages'],
    private: true
)]
class Page
{
    use TimestampableEntity;
    use BlameableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $body = null;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    #[Slug(fields:['title'])]
    private ?string $slug = null;

    #[ORM\ManyToMany(targetEntity: File::class)]
    private Collection $attachments;

    #[ORM\Column]
    private ?bool $published = null;

    public function __construct()
    {
        $this->attachments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection<int, File>
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(File $attachment): static
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments->add($attachment);
        }

        return $this;
    }

    public function removeAttachment(File $attachment): static
    {
        $this->attachments->removeElement($attachment);

        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(?bool $published): static
    {
        $this->published = $published;

        return $this;
    }
}
```

# CRUD Operation for an entity

- Define an index page
  - Index page should follow this structure
    - May change icons using i-tabler-{iconName}
    - May change button text
    - May change button url
    - May add new buttons depending on requirement
    - Should add Search component
    - List topic is not mandatory, should match with entity's broadcat topic
    - Loading lazy is required
    - ```twig
        {% extends 'base.html.twig' %}

        {% block actions %}
            <a href='{{ path('app_admin_page_new') }}' class="btn btn-outline">
                <i class="i-tabler-plus text-lg"></i>
                {% trans %}Add New Page{% endtrans %}
            </a>
        {% endblock %}

        {% block body %}
            {{ component('PagesSearchComponent', {listTopic: 'pages', loading: 'lazy', entityClass: 'App\\Entity\\Page\\Page', options: {'allow_remove': true, 'allow_bulk_remove': true}}) }}
        {% endblock %}
        ```
- Define an edit page
  - New page uses edit page with new entity
    - Place backlink to index page
    - Place LiveForm component with initialFormData attribute.
    - include delete form
    - ```twig
        {% extends 'admin/page/index.html.twig' %}
        {% block backLink %}
            <a href="{{ path('app_admin_page_index') }}" class="btn btn-outline">
                <i class="i-tabler-chevron-left"></i>
                {% trans %}Back{% endtrans %}
            </a>
        {% endblock %}
        {% block body %}
            <div class="flex flex-wrap p-3">
                <div class="md:w-9/12 w-full">				
                    {{ component('LiveForms:LivePageFormComponent', { initialFormData: page }) }}
                </div>
            </div>
            {% if page.id %}
                {{ include('admin/page/_delete_form.html.twig') }}
            {% endif %}
        {% endblock %}
        ```
- Define delete route
    - Delete form should be like this
    - Define form action path with entity id
    - Create csrf token to validate in controller
    - Inset a button to open delete modal. Modal automatically submits form after confirmation.
    - ```twig
        <form method="post" action="{{ path('app_admin_page_delete', {'id': page.id}) }}">
            <div class="fixed bottom-10 end-40 z-10">
                <input type="hidden" name="_token" value="{{ csrf_token('delete' ~ page.id) }}">
                {% set modalId = 'delete-modal-' ~ page.id %}
                <button type='button' class="btn btn-error btn-soft hover:text-base-100" data-controller="modal" data-modal-target-modal-value="{{ modalId }}">
                    <span class='i-tabler-trash text-xl'></span>
                </button>
            </div>
            {% include theme.themeDirectory ~ "/partials/_delete_modal.html.twig" with {modalId: modalId, objectName: "Page"|trans} %}
        </form>
        ```

```php
<?php

namespace App\Controller\Admin;

use App\Entity\Page\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/page')]
class PageController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator
    ) {
    }

    #[Route('/', name: 'app_admin_page_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/page/index.html.twig', [
            'title' => $this->translator->trans('Pages'),
        ]);
    }

    #[Route('/new', name: 'app_admin_page_new', methods: ['GET'])]
    public function new(): Response
    {
        $page = new Page();
        return $this->render('admin/page/edit.html.twig', [
            'title' => $this->translator->trans('Add New Page'),
            'page' => $page,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_page_edit', methods: ['GET'])]
    public function edit(Page $page): Response
    {
        return $this->render('admin/page/edit.html.twig', [
            'title' => $this->translator->trans('Edit Page {title}', ['title' => $page->getTitle()]),
            'page' => $page,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_page_delete', methods: ['POST'])]
    public function delete(Request $request, Page $page, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $page->getId(), $request->request->get('_token'))) {
            $entityManager->remove($page);
            $entityManager->flush();
            $this->addFlash('success', $this->translator->trans('Page deleted successfully.'));
        }

        return $this->redirectToRoute('app_admin_page_index', [], Response::HTTP_SEE_OTHER);
    }
}

```

## Create Search Component

- Place search components under `App\Twig\Components` namespace.
- Define default sorting with `$sort` and `$direction` parameters.
- Define row template file with `$rowTemplateFile`.
- Set default filtering if required in `getQueryBuilder` method. Return non filtered qury if there is no default filters.
- Prepare table build data.
  - headers: defines table headers
  - quick_filters: defines quick search columns. Do not leave emtpy, try to insert at least one filter here.
  - filters: defines detailed filters.
```php
<?php
namespace App\Twig\Components;

use App\Entity\Page\Page;
use Doctrine\ORM\QueryBuilder;
use Override;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent(template: '@base_theme/partials/_datatable.html.twig')]
final class PagesSearchComponent extends DatatableComponent
{
    #[LiveProp(writable: true, url: true)]
    public string $sort = 'p.createdAt';
    #[LiveProp(writable: true, url: true)]
    public string $direction = 'DESC';

    #[LiveProp(writable: false)]
    public ?string $rowTemplateFile = "admin/page/_row.html.twig";

    #[Override]
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->entityManager->getRepository(Page::class)->createQueryBuilder('p');
    }

    #[Override]
    public function getTableBuildData(): array
    {
        return [
            'headers' => [
                [
                    'label' => $this->translator->trans("Actions")
                ],
                'p.id' => [
                    'label' => $this->translator->trans("Id"),
                    'sortable' => true,
                ],
                'p.title' => [
                    'label' => $this->translator->trans("Title"),
                    'sortable' => true,
                ],
                [
                    'label' => $this->translator->trans('Published'),
                ],
                'p.createdAt' => [
                    'label' => $this->translator->trans('created_at'),
                    'sortable' => true
                ],
                'p.updatedAt' => [
                    'label' => $this->translator->trans('updated_at'),
                    'sortable' => true
                ]
            ],
            "quick_filters" => [
                "p.title" => []
            ],
            "filters" => [
                'p.title' => [
                    'options' => [
                        'label' => 'Title',
                    ],
                ],
                'p.published' => [
                    'type' => ChoiceType::class,
                    'options' => [
                        'label' => 'Published',
                        'placeholder' => $this->translator->trans('All'),
                        'choices' => [
                            'Published' => 1,
                            'Unpublished' => 0,
                        ],
                    ],
                ],
            ]
        ];
    }
}
```
Row file should something like this:

```html
    {% set action = action is defined ? action : null %}
    <tr id="page_{{ object.id }}" class='{{ action ? ' action-' ~ action }}'>
        {% if bulkRemoveAllowed is defined and bulkRemoveAllowed %}
            <td>
                <input type="checkbox" data-model="bulkSelectIds[]" value="{{ object.id }}" {{ object.id in this.bulkSelectIds ? 'checked' }} class="toggle toggle-primary mr-2 select-all"/>
            </td>
        {% else %}
            <td></td>
        {% endif %}
        <td class="px-6 py-3">
            <div class="flex gap-2">
                {% if action != 'remove' %}
                    <a class="btn btn-sm btn-outline" href="{{ path('app_admin_page_edit', {'id': object.id}) }}">
                        <i class="i-tabler-edit text-lg"></i>
                    </a>
                    {% if action == null %}
                        <form method="post" action="{{ path('app_admin_page_delete', {'id': object.id}) }}">
                            <input type="hidden" name="_token" value="{{ csrf_token('delete' ~ object.id) }}">
                            {% set modalId = 'delete-modal-' ~ object.id %}
                            <button type='button' class="btn btn-sm btn-error btn-outline" data-controller="modal" data-modal-target-modal-value="{{ modalId }}">
                                <i class="i-tabler-trash text-lg"></i>
                            </button>
                            {% include theme.themeDirectory ~ "/partials/_delete_modal.html.twig" with {modalId: modalId, objectName: "Page"|trans} %}
                        </form>
                    {% endif %}
                {% endif %}
            </div>
        </td>
        <td class="px-6 py-3">{{ object.id }}</td>
        <td class="px-6 py-3">{{ object.title }}</td>
        <td class="px-6 py-3">
            {% if object.published %}
                <span class="badge badge-success">
                    {% trans %}Published{% endtrans %}
                </span>
            {% else %}
                <span class="badge badge-warning">
                    {% trans %}Unpublished{% endtrans %}
                </span>
            {% endif %}

        </td>
        <td class='px-6 py-3 text-nowrap'>
            <time>{{ object.createdAt|format_datetime() }}</time>
        </td>
        <td class='px-6 py-3 text-nowrap'>
            <time>{{ object.updatedAt|format_datetime() }}</time>
        </td>
    </tr>
```

If entity uses broadcast attribute creating a stream file is required. And should something like this.
`templates/broadcast/Page/Page.stream.html.twig`
```twig
{% block create %}
	<turbo-stream action="prepend" target="pages">
		<template>
			{% include "admin/page/_row.html.twig" with {
				object: entity,
				action: "create"
			} %}
		</template>
	</turbo-stream>
{% endblock %}

{% block update %}
	<turbo-stream action="replace" target="page_{{ id }}">
		<template>
			{% include "admin/page/_row.html.twig" with {
				object: entity,
				action: "update"
			} %}
		</template>
	</turbo-stream>
{% endblock %}

{% block remove %}
	<turbo-stream action="replace" target="page_{{ id }}">
		<template>
			{% include "admin/page/_row.html.twig" with {
				object: entity,
				action: "remove"
            } %}
		</template>
	</turbo-stream>
{% endblock %}
```

## Create Live Form Component

Use symfony live components to create live forms. Try to use live forms as possible to create entity forms.
Setup success or error messages depending on entity and action.

```php
<?php

namespace App\Twig\Components\LiveForms;

use App\Entity\Page\Page;
use App\Form\Page\PageFormType;
use App\Twig\Components\FormType\LiveAsyncFileInputTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class LivePageFormComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use LiveAsyncFileInputTrait;

    #[LiveProp()]
    public ?Page $initialFormData = null;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected TranslatorInterface $translator,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        if (!$this->initialFormData) {
            $this->initialFormData = new Page();
        }
        $form = $this->createForm(PageFormType::class, $this->initialFormData, [
            'attr' => [
                'data-action' => 'live#action:prevent',
                'data-live-action-param' => 'save',
                'novalidate' => true
            ]
        ]);
        return $form;
    }

    #[LiveAction]
    public function save()
    {
        $this->submitForm();
        /** @var Page */
        $object = $this->getForm()->getData();
        $isCreate = !boolval($object->getId());
        $this->entityManager->persist($object);
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans(
            $isCreate ? 'Page created successfully.' : 'Page updated successfully.'
        ));
        if($isCreate){
            return $this->redirectToRoute('app_admin_page_edit', ['id' => $object->getId()]);
        }
    }
}
```

Create a twig file for entity like this in `templates/components/LiveForms`:

```twig
<div {{ attributes }}>
    {{ include(theme.themeDirectory ~ '/partials/_flash_messages.html.twig') }}
    {% include "admin/page/_form.html.twig" %}
</div>
```

Setup default form like this in `templates/{entityPath}/_form.html.twig`

```twig
{% set page = initialFormData %}
{% if page.id %}
	<div class='mb-3'>
		<label class='font-bold'>{% trans %}created_at{% endtrans %}</label>
		<div>{{ page.createdAt|format_datetime('full', 'short') }}</div>
	</div>
	<div class='mb-3'>
		<label class='font-bold'>{% trans %}updated_at{% endtrans %}</label>
		<div>{{ page.updatedAt|format_datetime('full', 'short') }}</div>
	</div>
{% endif %}
{{ form_start(form) }}
{{ form_widget(form) }}
<div class="fixed bottom-10 end-10">
	<button type="submit" class="btn btn-success btn-soft hover:text-base-100" data-loading="action(save)|addAttribute(disabled)">
		<i class="i-tabler-check text-xl"></i>
		{% trans %}Save{% endtrans %}
	</button>
</div>
{{ form_end(form) }}

```

# Add link to sidebar

Add proper link in proper location in `templates/themes/base_theme/partials/_sidebar.html.twig` with proper access checks if required.
For example page link like this:

<li>
    <a href="{{ path('app_admin_page_index') }}" data-turbo-frame="base-content">
        <i class="i-tabler-files"></i>
        <span>{% trans %}Pages{% endtrans %}</span>
    </a>
</li>
