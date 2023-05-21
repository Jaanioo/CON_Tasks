<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

class TextConverterController extends AbstractController
{
    #[Route('/upload', name: 'file_upload')]
    public function upload(): Response
    {
        return $this->render('text.html.twig');
    }

    #[Route('/convert', name: 'file_convert', methods: ['POST'])]
    public function convert(Request $request): Response
    {
        $file = $request->files->get('file');

        if (!$file) {
            return new Response('No file uploaded.', 400);
        }

        try {
            $content = file_get_contents($file->getPathname());

            $content = preg_replace('/{[^}]+}/', '', $content);

            $convertedText = $this->convertText($content);

            return $this->json([
                'originalText' => $content,
                'convertedText' => $convertedText,
            ]);
        } catch (\Exception $e) {
            return new Response('Failed to convert file.', 500);
        }
    }

    private function convertText(string $text): string
    {
        $process = new Process(['textutil', '-convert', 'html', '-stdin', '-stdout']);
        $process->setInput($text);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $html = $process->getOutput();

        $plainText = preg_replace('/<[^>]+>/', '', $html);

        $words = preg_split('/([\p{P}|\p{Z}])/u', $plainText, -1, PREG_SPLIT_DELIM_CAPTURE);
        $convertedWords = [];

        foreach ($words as $word) {
            if (mb_strlen($word) > 1) {
                $firstLetter = mb_substr($word, 0, 1);
                $lastLetter = mb_substr($word, -1);
                $middleLetters = mb_substr($word, 1, -1);
                $shuffledLetters = $this->shuffleLetters($middleLetters);

                $convertedWord = $firstLetter . $shuffledLetters . $lastLetter;
                $convertedWords[] = $convertedWord;
            } else {
                $convertedWords[] = $word;
            }
        }

        return implode('', $convertedWords);
    }

    /**
     * Losowo zamienia literki wewnątrz wyrazu.
     */
    private function shuffleLetters(string $letters): string
    {
        $lettersArray = preg_split('//u', $letters, -1, PREG_SPLIT_NO_EMPTY);
        shuffle($lettersArray);
        return implode('', $lettersArray);
    }
}
