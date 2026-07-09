import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";

const BACKGROUND_SRC =
  "/image-set/modern/fullscreen/pawel-czerwinski-IbHFznCKnqA-unsplash.jpg";

interface Hero78Props {
  className?: string;
}

const Hero78 = ({ className }: Hero78Props) => {
  return (
    <section
      className={cn(
        "dark relative flex h-svh max-h-[1400px] w-full overflow-hidden font-sans md:h-svh",
        className,
      )}
    >
      <img
        src={BACKGROUND_SRC}
        alt=""
        aria-hidden
        fetchPriority="high"
        decoding="sync"
        className="absolute inset-0 z-0 size-full object-cover object-center"
      />
      <div aria-hidden className="absolute inset-0 z-10 bg-black/20" />
      <div className="relative z-30 m-auto flex max-w-[46.25rem] flex-col items-center justify-center gap-6 px-5">
        <h1 className="text-center font-serif text-4xl leading-tight text-foreground md:text-6xl xl:text-[4.4rem]">
          Explore the wonders of science.
        </h1>
        <p className="text-center text-base text-foreground">
          From stunning skyscrapers to intricate bridges and innovative
          architectural marvels, each photo invites you to explore the
          artificial wonders of the world.
        </p>
        <Button className="h-fit w-fit rounded-full px-7 py-4 text-sm leading-tight font-medium">
          See all photos
        </Button>
      </div>
      <div className="pointer-events-none absolute inset-0 z-20 h-full w-full bg-[url('https://deifkwefumgah.cloudfront.net/shadcnblocks/block/patterns/noise.png')] bg-repeat opacity-15" />
    </section>
  );
};

export { Hero78 };
