<?php

namespace App\Form\Auth;

use App\Entity\Auth\User;
use App\Entity\Auth\UserStatus;
use App\Form\Type\AsyncFileType;
use App\Repository\Auth\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

class UserType extends AbstractType
{
    public function __construct(
        private Security $security,
        private UserRepository $userRepository
    ) {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $roleOptions = User::ROLES;
        if (!$this->security->isGranted(User::ROLE_SUPER_ADMIN)) {
            unset($roleOptions['Super Admin']);
        }
        $builder
            ->add('name')
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Email()
                ]
            ])->add("profile_photo", AsyncFileType::class, [
                "help" => 'Upload a PNG/JPG file.',
                'attr' => [
                    'accept' => implode(',', [
                        'image/png',
                        'image/jpg',
                        'image/jpeg',
                        'image/webp'
                    ])
                ]
            ])
            ->add('roles', ChoiceType::class, [
                "choices" => $roleOptions,
                "multiple" => true,
                "expanded" => true,
            ])->add('password', PasswordType::class, [
                'required' => false,
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])->add('status', EnumType::class, [
                'class' => UserStatus::class,
                'expanded' => true
            ]);
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
            $user = $event->getForm()->getData();
            if (
                !$this->security->isGranted(User::ROLE_SUPER_ADMIN) &&
                in_array(User::ROLE_SUPER_ADMIN, $user->getRoles())
            ) {
                $event->getForm()->add('roles', ChoiceType::class, [
                    "choices" => User::ROLES,
                    "multiple" => true,
                    "expanded" => true,
                    'choice_attr' => function ($key, $val, $index) {
                        if ($key == User::ROLE_SUPER_ADMIN) {
                            return !$this->security->isGranted(User::ROLE_SUPER_ADMIN) ? [
                                'disabled' => 'disabled'
                            ] : [];
                        } else {
                            return [];
                        }
                    }
                ]);
            }
        });
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            /** @var User */
            $user = $event->getForm()->getData();
            if (
                !$this->security->isGranted(User::ROLE_SUPER_ADMIN) &&
                in_array(User::ROLE_SUPER_ADMIN, $user->getRoles())
            ) {
                $data = $event->getData();
                $data["roles"] = array_merge(
                    [User::ROLE_SUPER_ADMIN],
                    $user->getRoles()
                );
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
