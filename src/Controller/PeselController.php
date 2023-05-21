<?php

namespace App\Controller;

use App\Validator\Pesel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @property $context
 */
class PeselController extends AbstractController
{
    #[Route('/pesel', name: 'validate_pesel')]
    public function validatePesel(Request $request, ValidatorInterface $validator): Response
    {
        $form = $this->createFormBuilder()
            ->add('pesel', TextType::class)
            ->add('submit', SubmitType::class, ['label' => 'Validate'])
            ->getForm();

        $form->handleRequest($request);

        $errorMessage = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $pesel = $form->getData()['pesel'];
            $constraint = new Pesel();
            $errors = $validator->validate($pesel, $constraint);

            if (count($errors) > 0) {
                $errorMessage = (string) $errors;
            } else {
                $errorMessage = "Numer PESEL jest poprawny!";
            }
        }

        return $this->render('pesel.html.twig', [
            'form' => $form->createView(),
            'errorMessage' => $errorMessage,
        ]);
    }
}
